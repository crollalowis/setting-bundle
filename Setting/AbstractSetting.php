<?php
/**
 * @package    agitation/settings
 * @link       http://github.com/agitation/AgitSettingsBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\SettingBundle\Setting;

use Agit\ValidationBundle\Exception\InvalidValueException;
use Agit\IntlBundle\Service\Translate;
use Agit\CoreBundle\Pluggable\Strategy\Combined\CombinedPluginInterface;
use Agit\CoreBundle\Pluggable\Strategy\Object\ServiceAwarePlugin;

abstract class AbstractSetting implements CombinedPluginInterface
{
    use ServiceAwarePlugin;

    protected $Translate;

    private $value;

    // used for storing instances during plugin registration
    private static $instances = [];

    private static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class]))
            self::$instances[$class] = new static(null);

        return self::$instances[$class];
    }

    public static function getPluginId()
    {
        return self::getInstance()->getId();
    }

    final public static function getSeeds($entityName)
    {
        return [
            [ 'id' => self::getInstance()->getId(), 'value' => self::getInstance()->getDefaultValue() ]
        ];
    }

    public function __construct($value)
    {
        $this->value = $value;
        $this->Translate = new Translate();
    }

    public function getServiceDependencies()
    {
        return ['agit.validation'];
    }

    final public function setValue($value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    final public function getValue()
    {
        return $this->value;
    }

    /**
     * Read-only settings cannot be edited through the API (i.e. by a "normal"
     * admin), but they can and have to be set programmatically by calling
     * SettingService::saveSetting directly with the $force parameter set to true.
     */
    public function isReadonly()
    {
        return false;
    }

    abstract public function getId();

    abstract public function getName();

    abstract public function getDefaultValue();

    /**
     * Must throw an exception if the value is invalid.
     */
    abstract public function validate($value);
}
