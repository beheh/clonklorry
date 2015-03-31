<?php

namespace Lorry\Model;

use Lorry\Model;

/*
 * @method \Lorry\Model\Release byId(int $id)
 * @method \Lorry\Model\Release[] byAnything()
 */

class Release extends Model
{

    public function getTable()
    {
        return 'release';
    }

    public function getSchema()
    {
        return array(
            'addon' => 'int',
            'version' => 'string',
            'timestamp' => 'datetime',
            'ready' => 'boolean',
            'shipping' => 'boolean',
            'assetsecret' => 'string',
            'changelog' => 'text',
            'whatsnew' => 'text');
    }

    final public function setAddon($addon)
    {
        return $this->setValue('addon', $addon);
    }

    /**
     * @return \Lorry\Model\Release[]
     */
    final public function byAddon($addon)
    {
        return $this->byValue('addon', $addon);
    }

    final public function getAddon()
    {
        return $this->getValue('addon');
    }

    /**
     * @return \Lorry\Model\Addon
     */
    final public function fetchAddon()
    {
        return $this->fetch('Addon', 'addon');
    }

    /**
     * @return \Lorry\Model\Release[]
     */
    final public function byGame($game)
    {
        $addons = $this->persistence->build('Addon')->all()->byGame($game);
        $releases = array();
        foreach ($addons as $addon) {
            $release = $this->persistence->build('Release')->latest($addon->getId());
            if ($release) {
                $releases[] = $release;
            }
        }
        return $releases;
    }

    /**
     * @return \Lorry\Model\Release[]
     */
    final public function byOwner($owner)
    {
        $addons = $this->persistence->build('Addon')->all()->byOwner($owner);
        $releases = array();
        foreach ($addons as $addon) {
            $release = $this->persistence->build('Release')->latest($addon->getId());
            if ($release) {
                $releases[] = $release;
            }
        }
        return $releases;
    }

    final public function setVersion($version)
    {
        $version = trim($version);
        $this->validateString($version, 1, 20);
        $this->validateRegexp($version, '/^([a-zA-Z0-9-][a-zA-Z0-9-.]*)$/');
        return $this->setValue('version', $version);
    }

    /**
     * @return \Lorry\Model\Release
     */
    final public function byVersion($version, $addon)
    {
        return $this->byValues(array('addon' => $addon, 'version' => $version));
    }

    final public function getVersion()
    {
        return $this->getValue('version');
    }

    final public function latest($addon)
    {
        $releases = $this->order('timestamp', true)->all()->byValue('addon',
            $addon);
        foreach ($releases as $release) {
            if (!$release->isReleased()) {
                continue;
            }
            return $release;
        }
        return null;
    }

    final public function isReleased()
    {
        if ($this->getTimestamp() === null) {
            return false;
        }

        if ($this->getTimestamp() > time()) {
            return false;
        }
        return true;
    }

    public function setTimestamp($timestamp)
    {
        return $this->setValue('timestamp', $timestamp);
    }

    public function getTimestamp()
    {
        return $this->getValue('timestamp');
    }

    public function isScheduled()
    {
        return $this->getTimestamp() !== null;
    }

    public function setWhatsnew($whatsnew)
    {
        if ($whatsnew) {
            $this->validateString($whatsnew, 5, 512);
        } else {
            $whatsnew = null;
        }
        return $this->setValue('whatsnew', $whatsnew);
    }

    public function getWhatsnew()
    {
        return $this->getValue('whatsnew');
    }

    public function setChangelog($changelog)
    {
        if ($changelog) {
            $this->validateString($changelog, 5, 65536);
        } else {
            $changelog = null;
        }
        return $this->setValue('changelog', $changelog);
    }

    public function getChangelog()
    {
        return $this->getValue('changelog');
    }

    public function fetchRequirements()
    {
        return $this->persistence->build('Dependency')->all()->byRelease($this->getId());
    }

    public function fetchDependencies()
    {
        return $this->persistence->build('Dependency')->all()->byRequired($this->getId());
    }

    public function onInsert()
    {
        $this->setValue('assetsecret', md5($this->getAddon().time().uniqid()));
    }

    public function getAssetSecret()
    {
        return $this->getValue('assetsecret');
    }

    public function setShipping($shipping)
    {
        $this->setValue('shipping', $shipping);
    }

    public function isShipping()
    {
        return $this->getValue('shipping');
    }
}
