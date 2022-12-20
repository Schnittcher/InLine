<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/BufferHelper.php';

        class InLineSchreibtischlampe extends IPSModule
        {
            use VariableProfileHelper;
            use MQTTHelper;
            use BufferHelper;

            public function Create()
            {
                //Never delete this line!
                parent::Create();
                $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

                $this->RegisterPropertyString('Topic', '');
                $this->RegisterPropertyString('FullTopic', '%prefix%/%topic%');
                $this->RegisterPropertyBoolean('MessageRetain', false);

                $this->createVariabenProfiles();
            }

            public function Destroy()
            {
                //Never delete this line!
                parent::Destroy();
            }

            public function ApplyChanges()
            {
                //Never delete this line!
                parent::ApplyChanges();

                $this->RegisterVariableBoolean('State', $this->Translate('State'), '~Switch', 0);
                $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 2);
                $this->RegisterVariableInteger('Colormode', $this->Translate('Colormode'), 'InLine.ColorMode', 3);
                $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 8);

                $this->EnableAction('State');
                $this->EnableAction('Brightness');
                $this->EnableAction('Colormode');
                $topic = $this->FilterFullTopicReceiveData();
                $this->SetReceiveDataFilter('.*' . $topic . '.*');
            }

            public function ReceiveData($JSONString)
            {
                if (!empty($this->ReadPropertyString('Topic'))) {
                    $this->SendDebug('ReceiveData JSON', $JSONString, 0);
                    $Buffer = json_decode($JSONString);

                    //Für MQTT Fix in IPS Version 6.3
                    if (IPS_GetKernelDate() > 1670886000) {
                        $Buffer->Payload = utf8_decode($Buffer->Payload);
                    }

                    $this->SendDebug('Topic', $Buffer->Topic, 0);

                    if (fnmatch('*LWT', $Buffer->Topic)) {
                        $this->SendDebug('LWT Payload', $Buffer->Payload, 0);
                        if (strtolower($Buffer->Payload) == 'online') {
                            SetValue($this->GetIDForIdent('DeviceStatus'), true);
                        } else {
                            SetValue($this->GetIDForIdent('DeviceStatus'), false);
                        }
                    }
                    if (fnmatch('*POWER', $Buffer->Topic)) {
                        if (fnmatch('*POWER', $Buffer->Topic)) {
                            $this->SetValue('State', $this->mappingOnOffValue($Buffer->Payload));
                        }
                    }
                    if (fnmatch('*mode', $Buffer->Topic)) {
                        if (fnmatch('*mode', $Buffer->Topic)) {
                            $this->SetValue('Colormode', $Buffer->Payload);
                        }
                    }
                    if (fnmatch('*RESULT', $Buffer->Topic)) {
                        $this->BufferResponse = $Buffer->Payload;
                        $Payload = json_decode($Buffer->Payload);
                        if (property_exists($Payload, 'POWER')) {
                            $this->SetValue('State', $this->mappingOnOffValue($Payload->POWER));
                        }
                        if (property_exists($Payload, 'Dimmer')) {
                            $this->SetValue('Brightness', $Payload->Dimmer);
                        }
                    }
                    if (fnmatch('*STATE', $Buffer->Topic)) {
                        $Payload = json_decode($Buffer->Payload);
                        if (property_exists($Payload, 'POWER')) {
                            $this->SetValue('State', $this->mappingOnOffValue($Payload->POWER));
                        }
                        if (property_exists($Payload, 'Dimmer')) {
                            $this->SetValue('Brightness', $Payload->Dimmer);
                        }
                    }
                }
            }

            public function RequestAction($Ident, $Value)
            {
                switch ($Ident) {
                    case 'State':
                        $command = 'POWER';
                        if ($Value === false) {
                            $msg = 'OFF';
                        } elseif ($Value === true) {
                            $msg = 'ON';
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Colormode':
                        $command = 'TuyaSend2';
                        switch ($Value) {
                            case 1:
                                $msg = '4,255';
                                break;
                            case 2:
                                $msg = '4,0';
                                break;
                            case 3:
                                $msg = '4,127';
                                break;
                            default:
                                $this->LogMessage('Wrong Colormode', KL_ERROR);
                                return;
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Brightness':
                        $command = 'Dimmer';
                        if (!$this->GetValue('State')) {
                            $this->MQTTCommand('POWER', 'ON');
                        }
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                }
            }

            private function mappingOnOffValue($Value)
            {
                switch ($Value) {
                    case 'ON':
                        return true;
                        break;
                    case 'OFF':
                        return false;
                        break;
                }
            }

            private function createVariabenProfiles()
            {
                //Speed Profile
                //Scheme Profile
                $this->RegisterProfileIntegerEx('InLine.ColorMode', 'Database', '', '', [
                    [1, $this->Translate('Default'),  '', -1],
                    [2, $this->Translate('Sepia'),  '', -1],
                    [3, $this->Translate('White'), '', -1]
                ]);
                //Online / Offline Profile
                $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
                    [false, 'Offline',  '', 0xFF0000],
                    [true, 'Online',  '', 0x00FF00]
                ]);
            }
        }
