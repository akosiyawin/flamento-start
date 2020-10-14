<?php
/**
 * Class Field
 * @package app\core\form
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core\form;


use app\core\database\Model;

abstract class Field
{
    protected Model $model;
    protected string $attribute;
    abstract public function renderInput() : string;

    /**
     * Field constructor.
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }

    //To be able to convert the whole class to string
    public function __toString()
    {
        return sprintf('
             <div class="form-group">
                 <label>%s</label>
                  %s
                 <div class="invalid-feedback" id="%s-invalid">
                    %s
                 </div>
             </div>
         ',
            $this->model->getLabel($this->attribute),
            $this->renderInput(),
            $this->attribute,
            $this->model->getFirstError($this->attribute)
        );
    }
}