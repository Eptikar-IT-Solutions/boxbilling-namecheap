<?php

// Copyright 2015 Eptikar IT Solutions Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * Boxbilling namecheap domain registrar adapter
 */
class Registrar_Adapter_Namecheap extends Registrar_AdapterAbstract
{
    protected $config;
    
    public function __construct($options)
    {
        if(isset($options['url']) && !empty($options['url'])) {
            $this->config['url'] = $options['url'];
            unset($options['url']);
        } else {
            throw new Registrar_Exception('Email Registrar config requires param "API URL"');
        }
        
        if(isset($options['UserName']) && !empty($options['UserName'])) {
            $this->config['UserName'] = $options['UserName'];
            unset($options['UserName']);
        } else {
            throw new Registrar_Exception('Email Registrar config requires param "User Name"');
        }
        
        if(isset($options['ApiUser']) && !empty($options['ApiUser'])) {
            $this->config['ApiUser'] = $options['ApiUser'];
            unset($options['ApiUser']);
        } else {
            throw new Registrar_Exception('Email Registrar config requires param "Api User"');
        }
        
        if(isset($options['ApiKey']) && !empty($options['ApiKey'])) {
            $this->config['ApiKey'] = $options['ApiKey'];
            unset($options['ApiKey']);
        } else {
            throw new Registrar_Exception('Email Registrar config requires param "Api Key"');
        }
        
        if(isset($options['ClientIp']) && !empty($options['ClientIp'])) {
            $this->config['ClientIp'] = $options['ClientIp'];
            unset($options['ClientIp']);
        } else {
            throw new Registrar_Exception('Email Registrar config requires param "Client IP"');
        }
    }

    public function getTlds()
    {
        return array('academy','accountants','actor','adult','agency','airforce','apartments','army','asia','associates','attorney','auction','audio','band','bar','bargains','beer','berlin','best','bid','bike','bingo','bio','biz','blackfriday','blue','boutique','build','builders','business','buzz','bz','ca','cab','camera','camp','capital','cards','care','careers','casa','cash','casino','catering','cc','center','ceo','ch','chat','cheap','christmas','church','city','claims','cleaning','click','clinic','clothing','club','cm','cn','co','co.com','co.uk','coach','codes','coffee','com','com.au','com.cn','com.es','com.pe','com.sg','community','company','computer','condos','construction','consulting','contractors','cooking','cool','country','credit','creditcard','cricket','cruises','cymru','dance','dating','de','de.com','deals','degree','delivery','democrat','dental','dentist','desi','design','diamonds','diet','digital','direct','directory','discount','domains','education','email','energy','engineer','engineering','enterprises','equipment','es','estate','eu','events','exchange','expert','exposed','fail','farm','fashion','finance','financial','fish','fishing','fit','fitness','flights','florist','flowers','football','forsale','foundation','fr','fund','furniture','futbol','gallery','garden','gift','gifts','gives','glass','global','graphics','gratis','gripe','guide','guitars','guru','haus','healthcare','help','hiphop','holdings','holiday','horse','host','hosting','house','how','immo','immobilien','in','industries','info','ink','institute','insure','international','investments','io','jetzt','jp.net','juegos','kaufen','kim','kitchen','kiwi','land','lawyer','lease','legal','li','life','lighting','limited','limo','link','loans','london','ltda','luxury','maison','management','market','marketing','me','me.uk','media','memorial','menu','mobi','moda','money','mortgage','nagoya','navy','net','net.au','net.cn','net.pe','network','ninja','nom.es','nu','nyc','okinawa','onl','org','org.au','org.cn','org.es','org.pe','org.uk','paris','partners','parts','party','pe','photo','photography','photos','physio','pics','pictures','pink','pizza','place','plumbing','poker','porn','press','productions','properties','property','pub','pw','recipes','red','rehab','reisen','rentals','repair','report','republican','rest','restaurant','reviews','rip','rocks','rodeo','sale','sarl','school','schule','science','services','sg','shiksha','shoes','singles','social','software','solar','solutions','soy','space','style','sucks','supplies','supply','support','surf','surgery','systems','tattoo','tax','technology','tennis','tienda','tips','tires','today','tokyo','tools','town','toys','trade','training','tv','uk','university','uno','us','us.com','us.org','vacation','vacations','vegas','ventures','vet','viajes','video','villas','vision','vodka','voting','voyage','wales','watch','webcam','website','wedding','wiki','work','works','world','ws','wtf','xn--3ds443g','xn--6frz82g','xn--fiq228c5hs','xxx','xyz','yoga','zone');
    }

    public static function getConfig()
    {
        return array(
            'label'     =>  'The Namecheap API allows you to build web and desktop applications that integrate with your Namecheap account. It allows you to programmatically perform operations like domain search, domain register, purchase SSL etc., from within your application.',
            'form'  => array(
                'url' => array("text",
                    array(
                        'label' => 'URL',
                        'description' => "e.g. https://api.namecheap.com/xml.response"
                    )
                ),
                'UserName' => array("text",
                    array(
                        'label' => 'Username',
                        'description' => "Enter your username here."
                    )
                ),
                'ApiUser' => array("text",
                    array(
                        'label' => 'API Username',
                        'description' => "Enter your API username here."
                    )
                ),
                'ApiKey' => array("text",
                    array(
                        'label' => 'API Key',
                        'description' => "Enter your API key here. To get your api key, go to Manage Profile section in Namecheap.com, then click API access link on the left hand side. C/p the key here. DON'T include your password."
                    )
                ),
                'ClientIp' => array("text",
                    array(
                        'label' => 'Client IP',
                        'description' => "Enter your client IP here."
                    )
            )
        )
        );
    }

    public function isDomainCanBeTransfered(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.getRegistrarLock', 'DomainName' => $domain->getName());
        $respond = $this->_call($params);
        if($respond === false) return false;
        $status = $respond->CommandResponse->DomainGetRegistrarLockResult->attributes();
        
        if(strtolower($status['RegistrarLockStatus']) == 'true') return false;
        else return true;
    }

    public function isDomainAvailable(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.check', 'DomainList' => $domain->getName());
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainCheckResult->attributes();
        
        if(strtolower($status['Available']) == 'true') return true;
        else return false;
    }

    public function modifyNs(Registrar_Domain $domain)
    {
        $nameServers = array();
        if(!is_null($domain->getNs1())) $nameServers[] = $domain->getNs1();
        if(!is_null($domain->getNs2())) $nameServers[] = $domain->getNs2();
        if(!is_null($domain->getNs3())) $nameServers[] = $domain->getNs3();
        if(!is_null($domain->getNs4())) $nameServers[] = $domain->getNs4();
        $nameServers = implode(",", $nameServers);
        
        $params = array('Command' => 'namecheap.domains.dns.setCustom', 'SLD' => $domain->getSld(), 'TLD' => $domain->getTld(false), 'NameServers' => $nameServers);
        $respond = $this->_call($params);
        if($respond === false) return false;
        $status = $respond->CommandResponse->DomainDNSSetCustomResult->attributes();
        
        if(strtolower($status['Update']) == 'true') return true;
        else return false;
    }

    public function transferDomain(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.transfer.create', 'DomainName' => $domain->getName(), 'EPPCode' => $domain->getEpp(), 'AddFreeWhoisguard' => 'yes', 'WGenable' => 'yes', 'Years' => 1);
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainTransferCreateResult->attributes();
        
        if(strtolower($status['Transfer']) == 'true') return true;
        else return false;
    }

    public function getDomainDetails(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.getInfo', 'DomainName' => $domain->getName());
        $respond = $this->_call($params);
        if($respond === false) return $domain;
        
        $CreatedDate = (string) $respond->CommandResponse->DomainGetInfoResult->DomainDetails->CreatedDate;
        $ExpiredDate = (string) $respond->CommandResponse->DomainGetInfoResult->DomainDetails->ExpiredDate;
        $Whoisguard = $respond->CommandResponse->DomainGetInfoResult->Whoisguard->attributes();
        $WhoisguardEnabled = false;

        if(strtolower($Whoisguard['Enabled']) == 'true') $WhoisguardEnabled = true;
        else $WhoisguardEnabled = false;
        
        error_log('$CreatedDate = ' . $CreatedDate . ' | strtotime = ' . strtotime($CreatedDate));
        error_log('$CreatedDate = ' . $ExpiredDate . ' | strtotime = ' . strtotime($ExpiredDate));
        
        $domain->setRegistrationTime(strtotime($CreatedDate));
        $domain->setExpirationTime(strtotime($ExpiredDate));
        $domain->setPrivacyEnabled($WhoisguardEnabled == 'true');
        return $domain;
    }

    public function deleteDomain(Registrar_Domain $domain)
    {
        return true;
    }

    public function registerDomain(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.create', 'DomainName' => $domain->getName(), 'AddFreeWhoisguard' => 'Yes', 'WGEnabled' => 'Yes', 'Years' => $domain->getRegistrationPeriod());
        
        $types = array('Registrant', 'Tech', 'Admin', 'AuxBilling');
        $contact = $domain->getContactRegistrar();
        
        foreach ($types as $type) {
            $params[$type.'FirstName'] = $contact->getFirstName();
            $params[$type.'LastName'] = $contact->getLastName();
            $params[$type.'Address1'] = $contact->getAddress1();
            $params[$type.'Address2'] = $contact->getAddress2();
            $params[$type.'Address3'] = $contact->getAddress3();
            $params[$type.'OrganizationName'] = $contact->getCompany();
            $params[$type.'JobTitle'] = $contact->getJobTitle();
            $params[$type.'City'] = $contact->getCity();
            $params[$type.'StateProvince'] = $contact->getState();
            $params[$type.'PostalCode'] = $contact->getZip();
            $params[$type.'Country'] = $contact->getCountry();
            $params[$type.'Phone'] = '+' . $contact->getTelCc() . '.' . $contact->getTel();
            $params[$type.'Fax'] = '+' . $contact->getFaxCc() . '.' . $contact->getFax();
            $params[$type.'EmailAddress'] = $contact->getEmail();
		}
        
        $nameServers = array();
        if(!is_null($domain->getNs1())) $nameServers[] = $domain->getNs1();
        if(!is_null($domain->getNs2())) $nameServers[] = $domain->getNs2();
        if(!is_null($domain->getNs3())) $nameServers[] = $domain->getNs3();
        if(!is_null($domain->getNs4())) $nameServers[] = $domain->getNs4();
        if(count($nameServers) > 0)$params['Nameservers'] = implode(",", $nameServers);
        
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainCreateResult->attributes();
        
        if(strtolower($status['Registered']) == 'true') return true;
        else return false;
    }

    public function renewDomain(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.renew', 'DomainName' => $domain->getName(), 'Years' => 1);
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainRenewResult->attributes();
        
        if(strtolower($status['Renew']) == 'true') return true;
        else return false;
    }

    public function modifyContact(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.setContacts', 'DomainName' => $domain->getName());
        
        $types = array('Registrant', 'Tech', 'Admin', 'AuxBilling');
        $contact = $domain->getContactRegistrar();
        
        foreach ($types as $type) {
            $params[$type.'FirstName'] = $contact->getFirstName();
            $params[$type.'LastName'] = $contact->getLastName();
            $params[$type.'Address1'] = $contact->getAddress1();
            $params[$type.'Address2'] = $contact->getAddress2();
            $params[$type.'Address3'] = $contact->getAddress3();
            $params[$type.'OrganizationName'] = $contact->getCompany();
            $params[$type.'JobTitle'] = $contact->getJobTitle();
            $params[$type.'City'] = $contact->getCity();
            $params[$type.'StateProvince'] = $contact->getState();
            $params[$type.'PostalCode'] = $contact->getZip();
            $params[$type.'Country'] = $contact->getCountry();
            $params[$type.'Phone'] = '+' . $contact->getTelCc() . '.' . $contact->getTel();
            $params[$type.'Fax'] = '+' . $contact->getFaxCc() . '.' . $contact->getFax();
            $params[$type.'EmailAddress'] = $contact->getEmail();
		}
        
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainSetContactResult->attributes();
        
        if(strtolower($status['IsSuccess']) == 'true') return true;
        else return false;
    }

    public function enablePrivacyProtection(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.getInfo', 'DomainName' => $domain->getName());
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $whoisguard_id = (string) $respond->CommandResponse->DomainGetInfoResult->Whoisguard->ID;
        
        $params = array('Command' => 'namecheap.whoisguard.enable', 'WhoisguardID' => $whoisguard_id, 'ForwardedToEmail' => $domain->getContactRegistrar()->getEmail());
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->WhoisguardEnableResult->attributes();
        
        if(strtolower($status['IsSuccess']) == 'true') return true;
        else return false;
    }

    public function disablePrivacyProtection(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.getInfo', 'DomainName' => $domain->getName());
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $whoisguard_id = (string) $respond->CommandResponse->DomainGetInfoResult->Whoisguard->ID;
        
        $params = array('Command' => 'namecheap.whoisguard.disable', 'WhoisguardID' => $whoisguard_id);
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->WhoisguardDisableResult->attributes();
        
        if(strtolower($status['IsSuccess']) == 'true') return true;
        else return false;
    }

    public function getEpp(Registrar_Domain $domain)
    {
        throw new Registrar_Exception('Epp code retrieval is not implemented');
    }

    public function lock(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.setRegistrarLock', 'DomainName' => $domain->getName(), 'LockAction' => 'lock');
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainSetRegistrarLockResult->attributes();
        
        if(strtolower($status['IsSuccess']) == 'true') return true;
        else return false;
    }

    public function unlock(Registrar_Domain $domain)
    {
        $params = array('Command' => 'namecheap.domains.setRegistrarLock', 'DomainName' => $domain->getName(), 'LockAction' => 'unlock');
        $respond = $this->_call($params);
        if($respond === false) return false;
        
        $status = $respond->CommandResponse->DomainSetRegistrarLockResult->attributes();
        
        if(strtolower($status['IsSuccess']) == 'true') return true;
        else return false;
    }
    
    private function _call($params)
    {
        $params['UserName'] = urlencode($this->config['UserName']);
        $params['ApiUser'] = urlencode($this->config['ApiUser']);
        $params['ApiKey'] = urlencode($this->config['ApiKey']);
        $params['ClientIp'] = urlencode($this->config['ClientIp']);
        
        $vars = array();
        foreach ($params as $key => $value) {
            $vars[] = urlencode($key) . '=' . urlencode($value);
        }
        $vars = implode("&", $vars);
        
        $url = $this->config['url']. '?' . $vars;
        $xml = simplexml_load_file($url);
        
		if ( 'ERROR' == $xml['Status'] )
		{
			$error = (string) $xml->Errors->Error;
            error_log('Namecheap URL: '.$url);
			error_log('Namecheap Error: '.$error);
            throw new Registrar_Exception($error);
            return false;
		} elseif ( 'OK' == $xml['Status'] )
		{
			return $xml;
		}
    }
}