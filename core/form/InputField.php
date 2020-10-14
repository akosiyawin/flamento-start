<?php
/**
 * Class InputField
 * @package app\core\form
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\form;


use app\core\Bull;
use app\core\database\Model;
use app\core\exception\UserException;

class InputField extends Field
{

    private string $type;

    /**
     * InputField constructor.
     * @param Model $model
     * @param string $attribute
     * @param string $type
     * @throws UserException
     */
    public function __construct(Model $model, string $attribute,string $type = Bull::TYPE_TEXT)
    {
        $this->findError($type);
        $this->type = $type;
        parent::__construct($model,$attribute);
    }

    public function passwordField()
    {
        $this->type = Bull::TYPE_PASSWORD;
        return $this;
    }

    public function emailField()
    {
        $this->type = Bull::TYPE_EMAIL;
        return $this;
    }
    public function numberField()
    {
        $this->type = Bull::TYPE_NUMBER;
        return $this;
    }

    public function renderInput(): string
    {
        return sprintf('<input  type="%s" id="%s-input" name="%s" class="form-control %s" value="%s">',
        $this->type,
        $this->attribute,
        $this->attribute,
        $this->model->hasError($this->attribute) ? "is-invalid" : "",
        $this->model->{$this->attribute} ?? null
        );
    }

    private function findError(string $type)
    {
        if( $type !== Bull::TYPE_TEXT   and
            $type !== Bull::TYPE_EMAIL  and
            $type !== Bull::TYPE_NUMBER and
            $type !== Bull::TYPE_PASSWORD )
        {
            throw new UserException("Invalid field type passed in '$type'",400);
        }
    }

}