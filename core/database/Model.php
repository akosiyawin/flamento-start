<?php
/**
 * Class Model
 * @package app\core\database
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\database;


use app\core\Application;
use app\core\Bull;
use app\core\constants\RULE;
use app\core\Validator;

abstract class Model extends DBModel
{
    public array $errors = [];
    public array $ruleRequired = [];
    public array $ruleEmail = [];
    public array $ruleUnique = [];
    public array $ruleMatch = [];
    //Make non static if error occured, not really needed to be static
    abstract public static function tableName(): string; //return "table"

    //Used for notice messages
    public function __toString()
    {
        return get_class($this);
    }

    //Representation of labels ['firstname' => 'First Name']
    public function labels() : array
    {
        return [];
    }

    public function rules() : array
    {
        return [];
    }

    public function getLabel(string $attr)
    {
        return $this->labels()[$attr] ?? $attr;
    }

    public function loadProperties(array $data)
    {
        foreach ($data as $key => $value)
        {
            if (property_exists($this,$key))
            {
                $this->{$key} = $value;
            }
        }
    }

    public function validate()
    {
        $this->validatePropertyRules();
        $this->validateRules();
        return empty($this->errors);
    }


    private function validatePropertyRules()
    {
        foreach ($this->ruleRequired as $attribute)
        {
            if (!$this->{$attribute})
            {
                $this->addRuleError($attribute,RULE::REQUIRED);
            }
        }
        foreach ($this->ruleEmail as $attribute)
        {
            if (!filter_var($this->{$attribute},FILTER_VALIDATE_EMAIL))
            {
                $this->addRuleError($attribute,RULE::EMAIL);
            }
        }
        foreach($this->ruleMatch as $key => $attributes)
        {
            if(is_string($attributes))
            {
                if($this->{$key} !== $this->{$attributes})
                {
                    $this->addRuleError($attributes,RULE::MATCH,['match' => $this->getLabel($key)]);
                }
            }
            else
            {
                foreach ($attributes as $attribute)
                {
                    if($this->{$key} !== $this->{$attribute})
                    {
                        $this->addRuleError($attribute,RULE::MATCH,['match' => $this->getLabel($key)]);
                    }
                }
            }
        }
        foreach($this->ruleUnique as $rule)
        {
            /** @var $class DBModel*/
            $class = new $rule['class'];
            $uniqeField = $rule['field'] ?? $rule['property']; //You can assign diff field, 'field' => 'email'
            $table = $class->tableName();
            $stmt = Application::$app->db->prepare("SELECT * from $table WHERE $uniqeField = :attr");
            $stmt->bindValue(":attr",$this->{$rule['property']});
            $stmt->execute();
            $results = $stmt->fetchObject();
            if($results)
            {
                $this->addRuleError($rule['property'],RULE::UNIQUE,['field' => $this->getLabel($rule['property'])]);
            }
        }
    }

    private function validateRules()
    {
        foreach ($this->rules() as $name => $rules)
        {
            $value = $this->{$name} ?? null; //actual value of the property
            foreach ($rules as $rule)
            {
                $ruleName = $rule;
                if(is_array($ruleName))
                {
                    $ruleName = $rule[0];
                }
                //Filters
                if ($ruleName === RULE::REQUIRED && !$value)
                {
                    $this->addRuleError($name,RULE::REQUIRED);
                }
                if ($ruleName === RULE::EMAIL && !filter_var($value,FILTER_VALIDATE_EMAIL))
                {
                    $this->addRuleError($name,RULE::EMAIL);
                }
                if ($ruleName === RULE::MIN && strlen($value) < $rule['min'])
                {
                    $this->addRuleError($name,RULE::MIN,$rule);
                }
                if ($ruleName === RULE::MAX && strlen($value) > $rule['max'])
                {
                    $this->addRuleError($name,RULE::MAX,$rule);
                }
                if($ruleName === RULE::MATCH && $value !== $this->{$rule['match']})
                {
                    $this->addRuleError($name,RULE::MATCH,['match' => $this->getLabel($rule['match'])]);
                }
                if($ruleName === RULE::UNIQUE)
                {
                    /** @var $class DBModel*/
                    $class = new $rule['class'];
                    $uniqeField = $rule['field'] ?? $name; //You can assign diff field, 'field' => 'email'
                    $table = $class->tableName();
                    $stmt = Application::$app->db->prepare("SELECT * from $table WHERE $uniqeField = :attr");
                    $stmt->bindValue(":attr",$value);
                    $stmt->execute();
                    $results = $stmt->fetchObject();
                    if($results)
                    {
                        $this->addRuleError($name,RULE::UNIQUE,['field' => $this->getLabel($name)]);
                    }
                }
            }
        }
    }

    private function addRuleError(string $name,string $rule,array $params = [])
    {
        $message = $this->errorMessages()[$rule];
        foreach ($params as $key => $value)
        {
            $message = str_replace("{{$key}}",$value,$message);
        }

        return $this->errors[$name][] = $message; //only one error for every attribute
    }

    public function addError(string $name, string $message)
    {
        return $this->errors[$name][] = $message;
    }

    private function errorMessages()
    {
        return [
            RULE::REQUIRED => "This field is required",
            RULE::EMAIL => "This field must be a valid Email address",
            RULE::MATCH => "This field must be the same as {match}",
            RULE::MIN => "This field must have at least {min} characters",
            RULE::MAX => "This field must not have at least {max} characters",
            RULE::UNIQUE => "Record with this {field} already exists",
        ];
    }

    public function hasError(string $name)
    {
        return $this->errors[$name] ?? false;
    }

    public function getFirstError(string $name)
    {
        return $this->errors[$name][0] ?? false; //only one error for every attribute
    }



}