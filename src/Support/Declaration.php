<?php
/**
 * This file is part of Properties package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 14.04.2016 13:29
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Serafim\Properties\Support;

/**
 * Class Declaration
 * @package Serafim\Properties\Support
 */
class Declaration
{
    /**
     * @var array|string[]
     */
    private $types = [];

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $access;

    /**
     * @var string
     */
    private $originalTypesDeclaration = '';

    /**
     * Declaration constructor.
     * @param string $types
     * @param string $field
     * @param string $accessType
     */
    public function __construct($types, $field, $accessType = Parser::ACCESS_BOTH)
    {
        $this->originalTypesDeclaration = trim($types);
        $this->types = $this->getTypesFromDeclaration($this->originalTypesDeclaration);
        $this->field = $field;
        $this->access = $accessType;
    }

    /**
     * @param string $originalTypesDeclaration
     * @return array
     */
    private function getTypesFromDeclaration($originalTypesDeclaration)
    {
        $types = explode('|', mb_strtolower($originalTypesDeclaration));

        // Some[] to array
        foreach ($types as $typeKey => $typeName) {
            $types[$typeKey] = preg_replace("/(.+)\[\]/i", "array", $typeName);
        }

        // Some<Type> to Some
        foreach ($types as $typeKey => $typeName) {
            $types[$typeKey] = preg_replace("/(.+)\<(.+)\>/i", "$1", $typeName);
        }

        return $types;
    }

    /**
     * @return string
     */
    public function getAvailableTypes()
    {
        return $this->originalTypesDeclaration;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function typeOf($type)
    {

        if ($this->inTypesArray($type)) {
            return true;
        }

        $typeAliases = $this->getVarTypeAliases($type);

        foreach ($typeAliases as $typeAliase) {
            if ($this->inTypesArray($typeAliase)) {
                return true;
            }
        }

        return $this->classOf($type);
    }

    /**
     * @param $type
     * @return bool
     */
    private function inTypesArray($type)
    {
        return in_array(mb_strtolower($type), $this->types, true);
    }

    /**
     * @param $typeName
     * @return array
     */
    private function getVarTypeAliases($typeName)
    {
        $typeName = strtolower($typeName);

        $types = [
            'integer'  => [
                'int',
            ],
            'boolean'  => [
                'bool',
            ],
            'closure'  => [
                'callable',
            ],
            'double'   => [
                'float',
            ],
            'string'   => [],
            'null'     => [],
            'resource' => [],
        ];

        return isset($types[$typeName]) ? $types[$typeName] : [];
    }

    /**
     * @param string $type
     * @return bool
     */
    private function classOf($type)
    {
        foreach ($this->types as $class) {
            if (is_subclass_of($type, $class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->access === Parser::ACCESS_READ || $this->access === Parser::ACCESS_BOTH;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->access === Parser::ACCESS_WRITE || $this->access === Parser::ACCESS_BOTH;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->field;
    }
}
