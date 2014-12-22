<?php

namespace Aurimas\IssuesBundle\Model;

/**
 * Class BaseModel
 * @package Aurimas\IssuesBundle\Model
 * @author Aurimas Niekis <aurimas.niekis@gmail.com>
 */
class BaseModel
{
    public static function loadFromResponse(array $response)
    {
        $class = get_called_class();
        $model = new $class();
        $model->loadData($response);

        return $model;
    }

    public function loadData(array $response, $prefix = null)
    {
        foreach ($response as $property => $value) {
            if ($prefix) {
                $property = $prefix . '_' . $property;
            }

            if (is_array($value)) {
                $this->loadData($value, $property);
            } else {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                } elseif (property_exists($this, $this->camelCase($property))) {
                    $property = $this->camelCase($property);
                    $this->$property = $value;
                }
            }
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function camelCase($value)
    {
        return preg_replace_callback(
            '/_(.?)/',
            function ($value) {
                return strtoupper($value[1]);
            },
            strtolower($value)
        );
    }
}
