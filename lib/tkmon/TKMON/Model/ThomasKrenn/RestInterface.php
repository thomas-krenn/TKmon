<?php
/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2015 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Model\ThomasKrenn;

use NETWAYS\Http\SimpleProxy;
use TKMON\Exception\ModelException;
use TKMON\Model\ApplicationModel;
use TKMON\Model\User;

/**
 * Model to retrieve data from thomas krenn rest service
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class RestInterface extends ApplicationModel
{

    /**
     * AuthKey to talk to Thomas Krenn
     * @var string
     */
    private $authKey;

    /**
     * Language key for request
     * @var string
     */
    private $lang;

    /**
     * Setter for authKey
     * @param string $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * Getter for authKey
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Getter for lang
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Configure lang from user object
     * @param User $user
     * @return bool
     * @throws \TKMON\Exception\ModelException
     */
    public function setLangFromUserObject(User $user)
    {
        $m = array();
        if (preg_match('/^(\w{2})_/', $user->getLocale(), $m)) {
            $this->setLang($m[1]);
            return true;
        }

        throw new ModelException('Could not detect lang from user locale: '. $user->getLocale());
    }

    /**
     * Setter for lang
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Tests the object or proper configured
     * @throws \TKMON\Exception\ModelException
     */
    private function assertConfiguration()
    {
        if (!$this->authKey) {
            throw new ModelException('$authKey is mandatory to run operations');
        }

        if (!$this->lang) {
            throw new ModelException('$lang is mandatory');
        }
    }

    /**
     * Wraps request with language attribute
     * @param $requestUrl
     * @return string
     */
    private function wrapRequestUrl($requestUrl)
    {
        return $requestUrl. '?lang='. $this->getLang();
    }

    /**
     * Requests product structure from webservice
     * @param $serialNo
     * @return \stdClass
     * @throws \TKMON\Exception\ModelException
     */
    public function getProductObject($serialNo)
    {
        $this->assertConfiguration();
        $proxy = new SimpleProxy($this->container['config']['thomaskrenn.rest.serial']);
        $proxy->setHttpAuth($this->getAuthKey(), '');
        $proxy->setRequestUrl($this->wrapRequestUrl('/'. $serialNo));

        $dom = new \DOMDocument('1.0', 'utf-8');
        $checkLoad = $dom->loadXML($proxy->getContent());

        if (!$checkLoad === true) {
            throw new ModelException('Could not fetch any data from rest service');
        }

        $products = $dom->getElementsByTagName('product');

        if ($products->length == 0) {
            throw new ModelException('No product found');
        }

        /** @var \DOMElement $product */
        $product = $products->item(0);

        $struct = new \stdClass();
        $struct->productId = (int)trim($product->textContent);
        $struct->productLink = trim($product->getAttribute('resource'));

        return $struct;
    }

    /**
     * Returns the product id for a serial number
     * @param string $serialNo
     * @return integer
     */
    public function getProductIdForSerial($serialNo)
    {
        $object = $this->getProductObject($serialNo);
        return $object->productId;
    }

    /**
     * Returns product link for serial number
     * @param string $serialNo
     * @return string
     */
    public function getProductLinkForServial($serialNo)
    {
        $object = $this->getProductObject($serialNo);
        return $object->productLink;
    }

    /**
     * Returns detail information about products
     * @param $productId
     * @return \stdClass
     */
    public function getProductDetail($productId)
    {
        $proxy = new SimpleProxy($this->container['config']['thomaskrenn.rest.product']);
        $proxy->setHttpAuth($this->getAuthKey(), '');
        $proxy->setRequestUrl($this->wrapRequestUrl('/'. $productId));

        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadXML($proxy->getContent());
        $products = $dom->getElementsByTagName('product');
        $product = $products->item(0);

        $object = new \stdClass();
        foreach ($product->childNodes as $item) {
            if ($item->nodeType === XML_ELEMENT_NODE) {
                $object->{ $item->nodeName } = trim($item->textContent);
            }
        }

        return $object;
    }

    /**
     * Product detail from serialNo
     * @param $serialNo
     * @return \stdClass
     */
    public function getProductDetailFromSerial($serialNo)
    {
        $productId = $this->getProductIdForSerial($serialNo);
        $detail = $this->getProductDetail($productId);
        return $detail;
    }

    /**
     * Get the wiki url for serial no
     * @param string $serialNo
     * @return string
     */
    public function getWikiLinkForSerial($serialNo)
    {
        // No assertion needed, comes now!
        $this->getProductIdForSerial($serialNo);
        return $detail->wiki_link;
    }
}
