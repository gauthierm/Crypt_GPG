<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains a class representing GPG sub-keys and constants for GPG algorithms
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @copyright 2005-2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Crypt_GPG
 */

// {{{ class Crypt_GPG_SubKey

/**
 * A class for GPG sub-key information
 *
 * This class is used to store the results of the {@link Crypt_GPG::getKeys()}
 * method. Sub-key objects are members of a {@link Crypt_GPG_Key} object.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @copyright 2005-2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @see       Crypt_GPG::getKeys()
 * @see       Crypt_GPG_Key::getSubKeys()
 */
class Crypt_GPG_SubKey
{
    // {{{ class constants

    /**
     * RSA encryption algorithm.
     */
    const ALGORITHM_RSA = 1;

    /**
     * Elgamal encryption algorithm (encryption only).
     */
    const ALGORITHM_ELGAMAL_ENC = 16;

    /**
     * DSA encryption algorithm (sometimes called DH, sign only).
     */
    const ALGORITHM_DSA = 17;

    /**
     * Elgamal encryption algorithm (signage and encryption - should not be
     * used).
     */
    const ALGORITHM_ELGAMAL_ENC_SGN = 20;

    // }}}
    // {{{ class properties

    /**
     * The id of this sub-key
     *
     * @var string
     */
    private $_id = '';

    /**
     * The algorithm used to create this sub-key
     *
     * The value is one of the Crypt_GPG_SubKey::ALGORITHM_* constants.
     *
     * @var integer
     */
    private $_algorithm = 0;

    /**
     * The fingerprint of this sub-key
     *
     * @var string
     */
    private $_fingerprint = '';

    /**
     * Length of this sub-key in bits
     *
     * @var integer
     */
    private $_length = 0;

    /**
     * Date this sub-key was created
     *
     * This is a Unix timestamp.
     *
     * @var integer
     */
    private $_creationDate = 0;

    /**
     * Date this sub-key expires
     *
     * This is a Unix timestamp. If this sub-key does not expire, this will be
     * zero.
     *
     * @var integer
     */
    private $_expirationDate = 0;

    /**
     * Whether or not this sub-key can sign data
     *
     * @var boolean
     */
    private $_canSign = false;

    /**
     * Whether or not this sub-key can encrypt data
     *
     * @var boolean
     */
    private $_canEncrypt = false;

    /**
     * Whether or not the private key for this sub-key exists in the keyring
     *
     * @var boolean
     */
    private $_hasPrivate = false;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new sub-key object
     *
     * Sub-keys can be initialized from an array of named values. Available
     * names are:
     *
     * - <kbd>string  id</kbd>          - the key id of the sub-key.
     * - <kbd>integer algorithm</kbd>   - the encryption algorithm of the
     *                                    sub-key.
     * - <kbd>string  fingerprint</kbd> - the fingerprint of the sub-key. The
     *                                    fingerprint should not contain
     *                                    formatting characters.
     * - <kbd>integer length</kbd>      - the length of the sub-key in bits.
     * - <kbd>integer creation</kbd>    - the date the sub-key was created.
     *                                    This is a UNIX timestamp.
     * - <kbd>integer expiration</kbd>  - the date the sub-key expires. This
     *                                    is a UNIX timestamp. If the sub-key
     *                                    does not expire, use 0.
     * - <kbd>boolean canSign</kbd>     - whether or not the sub-key can be
     *                                    used to sign data.
     * - <kbd>boolean canEncrypt</kbd>  - whether or not the sub-key can be
     *                                    used to encrypt data.
     * - <kbd>boolean hasPrivate</kbd>  - whether or not the private key for
     *                                    the sub-key exists in the keyring.
     *
     * @param Crypt_GPG_SubKey|string|array $key optional. Either an existing
     *        sub-key object, which is copied; a sub-key string, which is
     *        parsed; or an array of initial values.
     */
    public function __construct($key = null)
    {
        // parse from string
        if (is_string($key)) {
            $key = self::parse($key);
        }

        // copy from object
        if ($key instanceof Crypt_GPG_SubKey) {
            $this->_id             = $key->_id;
            $this->_algorithm      = $key->_algorithm;
            $this->_fingerprint    = $key->_fingerprint;
            $this->_length         = $key->_length;
            $this->_creationDate   = $key->_creationDate;
            $this->_expirationDate = $key->_expirationDate;
            $this->_canSign        = $key->_canSign;
            $this->_canEncrypt     = $key->_canEncrypt;
            $this->_hasPrivate     = $key->_hasPrivate;
        }

        // initialize from array
        if (is_array($key)) {
            if (array_key_exists('id', $key)) {
                $this->setId($key['id']);
            }

            if (array_key_exists('algorithm', $key)) {
                $this->setAlgorithm($key['algorithm']);
            }

            if (array_key_exists('fingerprint', $key)) {
                $this->setFingerprint($key['fingerprint']);
            }

            if (array_key_exists('length', $key)) {
                $this->setLength($key['length']);
            }

            if (array_key_exists('creation', $key)) {
                $this->setCreationDate($key['creation']);
            }

            if (array_key_exists('expiration', $key)) {
                $this->setExpirationDate($key['expiration']);
            }

            if (array_key_exists('canSign', $key)) {
                $this->setCanSign($key['canSign']);
            }

            if (array_key_exists('canEncrypt', $key)) {
                $this->setCanEncrypt($key['canEncrypt']);
            }

            if (array_key_exists('hasPrivate', $key)) {
                $this->setHasPrivate($key['hasPrivate']);
            }
        }
    }

    // }}}
    // {{{ getId()

    /**
     * Gets the id of this sub-key
     *
     * @return string the id of this sub-key.
     */
    public function getId()
    {
        return $this->_id;
    }

    // }}}
    // {{{ getAlgorithm()

    /**
     * Gets the algorithm used by this sub-key
     *
     * The algorithm should be one of the Crypt_GPG_SubKey::ALGORITHM_*
     * constants.
     *
     * @return integer the algorithm used by this sub-key.
     */
    public function getAlgorithm()
    {
        return $this->_algorithm;
    }

    // }}}
    // {{{ getCreationDate()

    /**
     * Gets the creation date of this sub-key
     *
     * This is a Unix timestamp.
     *
     * @return integer the creation date of this sub-key.
     */
    public function getCreationDate()
    {
        return $this->_creationDate;
    }

    // }}}
    // {{{ getExpirationDate()

    /**
     * Gets the date this sub-key expires
     *
     * This is a Unix timestamp. If this sub-key does not expire, this will be
     * zero.
     *
     * @return integer the date this sub-key expires.
     */
    public function getExpirationDate()
    {
        return $this->_expirationDate;
    }

    // }}}
    // {{{ getFingerprint()

    /**
     * Gets the fingerprint of this sub-key
     *
     * @return string the fingerprint of this sub-key.
     */
    public function getFingerprint()
    {
        return $this->_fingerprint;
    }

    // }}}
    // {{{ getLength()

    /**
     * Gets the length of this sub-key in bits
     *
     * @return integer the length of this sub-key in bits.
     */
    public function getLength()
    {
        return $this->_length;
    }

    // }}}
    // {{{ canSign()

    /**
     * Gets whether or not this sub-key can sign data
     *
     * @return boolean true if this sub-key can sign data and false if this
     *                 sub-key can not sign data.
     */
    public function canSign()
    {
        return $this->_canSign;
    }

    // }}}
    // {{{ canEncrypt()

    /**
     * Gets whether or not this sub-key can encrypt data
     *
     * @return boolean true if this sub-key can encrypt data and false if this
     *                 sub-key can not encrypt data.
     */
    public function canEncrypt()
    {
        return $this->_canEncrypt;
    }

    // }}}
    // {{{ hasPrivate()

    /**
     * Gets whether or not the private key for this sub-key exists in the
     * keyring
     *
     * @return boolean true the private key for this sub-key exists in the
     *                 keyring and false if it does not.
     */
    public function hasPrivate()
    {
        return $this->_hasPrivate;
    }

    // }}}
    // {{{ setCreationDate()

    /**
     * Sets the creation date of this sub-key
     *
     * The creation date is a Unix timestamp.
     *
     * @param integer $creationDate the creation date of this sub-key.
     *
     * @return void
     */
    public function setCreationDate($creationDate)
    {
        $this->_creationDate = intval($creationDate);
    }

    // }}}
    // {{{ setExpirationDate()

    /**
     * Sets the expiration date of this sub-key
     *
     * The expiration date is a Unix timestamp. Specify zero if this sub-key
     * does not expire.
     *
     * @param integer $expirationDate the expiration date of this sub-key.
     *
     * @return void
     */
    public function setExpirationDate($expirationDate)
    {
        $this->_expirationDate = intval($expirationDate);
    }

    // }}}
    // {{{ setId()

    /**
     * Sets the id of this sub-key
     *
     * @param string $id the id of this sub-key.
     *
     * @return void
     */
    public function setId($id)
    {
        $this->_id = strval($id);
    }

    // }}}
    // {{{ setAlgorithm()

    /**
     * Sets the algorithm used by this sub-key
     *
     * @param integer $algorithm the algorithm used by this sub-key.
     *
     * @return void
     */
    public function setAlgorithm($algorithm)
    {
        $this->_algorithm = intval($algorithm);
    }

    // }}}
    // {{{ setFingerprint()

    /**
     * Sets the fingerprint of this sub-key
     *
     * @param string $fingerprint the fingerprint of this sub-key.
     *
     * @return void
     */
    public function setFingerprint($fingerprint)
    {
        $this->_fingerprint = strval($fingerprint);
    }

    // }}}
    // {{{ setLength()

    /**
     * Sets the length of this sub-key in bits
     *
     * @param integer $length the length of this sub-key in bits.
     *
     * @return void
     */
    public function setLength($length)
    {
        $this->_length = intval($length);
    }

    // }}}
    // {{{ setCanSign()

    /**
     * Sets whether of not this sub-key can sign data
     *
     * @param boolean $canSign true if this sub-key can sign data and false if
     *                         it can not.
     *
     * @return void
     */
    public function setCanSign($canSign)
    {
        $this->_canSign = ($canSign) ? true : false;
    }

    // }}}
    // {{{ setCanEncrypt()

    /**
     * Sets whether of not this sub-key can encrypt data
     *
     * @param boolean $canEncrypt true if this sub-key can encrypt data and
     *                            false if it can not.
     *
     * @return void
     */
    public function setCanEncrypt($canEncrypt)
    {
        $this->_canEncrypt = ($canEncrypt) ? true : false;
    }

    // }}}
    // {{{ setHasPrivate()

    /**
     * Sets whether of not the private key for this sub-key exists in the
     * keyring
     *
     * @param boolean $hasPrivate true if the private key for this sub-key
     *                            exists in the keyring and false if it does
     *                            not.
     *
     * @return void
     */
    public function setHasPrivate($hasPrivate)
    {
        $this->_hasPrivate = ($hasPrivate) ? true : false;
    }

    // }}}
    // {{{ parse()

    /**
     * Parses a sub-key object from a sub-key string
     *
     * See <b>doc/DETAILS</b> in the
     * {@link http://www.gnupg.org/download/ GPG distribution} for information
     * on how the sub-key string is parsed.
     *
     * @param string $string the string containing the sub-key.
     *
     * @return Crypt_GPG_SubKey the sub-key object parsed from the string.
     */
    public static function parse($string)
    {
        $tokens = explode(':', $string);

        $subKey = new Crypt_GPG_SubKey();

        $subKey->setId($tokens[4]);
        $subKey->setLength($tokens[2]);
        $subKey->setAlgorithm($tokens[3]);
        $subKey->setCreationDate(self::_parseDate($tokens[5]));
        $subKey->setExpirationDate(self::_parseDate($tokens[6]));

        if (strpos($tokens[11], 's') !== false) {
            $subKey->setCanSign(true);
        }

        if (strpos($tokens[11], 'e') !== false) {
            $subKey->setCanEncrypt(true);
        }

        return $subKey;
    }

    // }}}
    // {{{ _parseDate()

    /**
     * Parses a date string as provided by GPG into a UNIX timestamp
     *
     * @param string $string the date string.
     *
     * @return integer the UNIX timestamp corresponding to the provided date
     *                 string.
     */
    private static function _parseDate($string)
    {
        if ($string == '') {
            $timestamp = 0;
        } else {
            // all times are in UTC according to GPG documentation
            $timeZone = new DateTimeZone('UTC');

            if (strpos($string, 'T') === false) {
                // interpret as UNIX timestamp
                $string = '@' . $string;
            }

            $date = new DateTime($string, $timeZone);

            // convert to UNIX timestamp
            $timestamp = intval($date->format('U'));
        }

        return $timestamp;
    }

    // }}}
}

// }}}

?>