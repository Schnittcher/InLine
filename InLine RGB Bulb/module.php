<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/BufferHelper.php';

        class InLineRGBBulb extends IPSModule
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

                $this->RegisterVariableBoolean('StateRGB', $this->Translate('State RGB'), '~Switch', 0);
                $this->RegisterVariableBoolean('StateWhite', $this->Translate('State White'), '~Switch', 0);
                $this->RegisterVariableInteger('Color', $this->Translate('Color'), '~HexColor', 1);
                $this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 2);
                $this->RegisterVariableBoolean('Fade', $this->Translate('Fade'), '~Switch', 3);
                $this->RegisterVariableInteger('Effect', $this->Translate('Effect'), 'InLine.LEDEffect', 4);
                $this->RegisterVariableInteger('Speed', $this->Translate('Speed'), 'InLine.LEDSpeed', 5);
                $this->RegisterVariableInteger('White', $this->Translate('White'), '~Intensity.100', 6);
                $this->RegisterVariableInteger('CT', $this->Translate('Color Temperature'), 'InLine.CT', 7);
                $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 8);

                $this->EnableAction('StateRGB');
                $this->EnableAction('StateWhite');
                $this->EnableAction('Color');
                $this->EnableAction('Fade');
                $this->EnableAction('Fade');
                $this->EnableAction('Effect');
                $this->EnableAction('Brightness');
                $this->EnableAction('Speed');
                $this->EnableAction('White');
                $this->EnableAction('CT');
                $topic = $this->FilterFullTopicReceiveData();
                $this->SetReceiveDataFilter('.*' . $topic . '.*');
            }

            public function ReceiveData($JSONString)
            {
                if (!empty($this->ReadPropertyString('Topic'))) {
                    $this->SendDebug('ReceiveData JSON', $JSONString, 0);
                    $data = json_decode($JSONString);

                    switch ($data->DataID) {
                        case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                            $Buffer = $data;
                            break;
                        case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                            $Buffer = json_decode($data->Buffer);
                            break;
                        default:
                            $this->LogMessage('Invalid Parent', KL_ERROR);
                            return;
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
                    if (fnmatch('*STATE', $Buffer->Topic)) {
                        $Payload = json_decode($Buffer->Payload);
                        if (property_exists($Payload, 'Color')) {
                            $rgb = explode(',', $Payload->Color);
                            $color = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                            $color = ltrim($color, '#');
                            $this->SetValue('Color', hexdec($color));
                        }
                        if (property_exists($Payload, 'Dimmer1')) {
                            $this->SetValue('Brightness', $Payload->Dimmer1);
                        }

                        if (property_exists($Payload, 'Dimmer2')) {
                            $this->SetValue('White', $Payload->Dimmer2);
                        }
                        if (property_exists($Payload, 'CT')) {
                            $this->SetValue('CT', $Payload->CT);
                        }
                        if (property_exists($Payload, 'POWER1')) {
                            $this->SetValue('StateRGB', $this->mappingOnOffValue($Payload->POWER1));
                        }
                        if (property_exists($Payload, 'Fade')) {
                            $this->SetValue('Fade', $this->mappingOnOffValue($Payload->Fade));
                        }
                        if (property_exists($Payload, 'Scheme')) {
                            $this->SetValue('Effect', $Payload->Scheme);
                        }
                        if (property_exists($Payload, 'POWER2')) {
                            $this->SetValue('StateWhite', $this->mappingOnOffValue($Payload->POWER2));
                        }
                    }
                    if (fnmatch('*RESULT', $Buffer->Topic)) {
                        $this->BufferResponse = $Buffer->Payload;
                        $Payload = json_decode($Buffer->Payload);

                        if (property_exists($Payload, 'POWER1')) {
                            $this->SendDebug('POWER1', $Payload->POWER1, 0);
                            $this->SetValue('StateRGB', $this->mappingOnOffValue($Payload->POWER1));
                        }
                        if (property_exists($Payload, 'POWER2')) {
                            $this->SetValue('StateWhite', $this->mappingOnOffValue($Payload->POWER2));
                        }
                        if (property_exists($Payload, 'Speed')) {
                            $this->SetValue('Speed', $Payload->Speed);
                        }
                        if (property_exists($Payload, 'Scheme')) {
                            $this->SetValue('Effect', $Payload->Scheme);
                        }
                        if (property_exists($Payload, 'Dimmer1')) {
                            $this->SetValue('Brightness', $Payload->Dimmer1);
                        }
                        if (property_exists($Payload, 'Color')) {
                            $rgb = explode(',', $Payload->Color);
                            $color = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                            $color = ltrim($color, '#');
                            $this->SetValue('Color', hexdec($color));
                        }
                        if (property_exists($Payload, 'Fade')) {
                            $this->SetValue('Fade', $this->mappingOnOffValue($Payload->Fade));
                        }
                        if (property_exists($Payload, 'White')) {
                            $this->SetValue('White', $Payload->White);
                        }
                        if (property_exists($Payload, 'CT')) {
                            $this->SetValue('CT', $Payload->CT);
                        }
                    }
                }
            }

            public function RequestAction($Ident, $Value)
            {
                switch ($Ident) {
                    case 'StateRGB':
                        $command = 'POWER1';
                        if ($Value === false) {
                            $msg = 'OFF';
                        } elseif ($Value === true) {
                            $msg = 'ON';
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'StateWhite':
                        $command = 'POWER2';
                        if ($Value === false) {
                            $msg = 'OFF';
                        } elseif ($Value === true) {
                            $msg = 'ON';
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Fade':
                        $command = 'Fade';
                        if ($Value === false) {
                            $msg = 'OFF';
                        } elseif ($Value === true) {
                            $msg = 'ON';
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Effect':
                        $command = 'Scheme';
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Brightness':
                        $command = 'Dimmer';
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Speed':
                        $command = 'Speed';
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Color':
                        $rgb = $Value;
                        $r = (($rgb >> 16) & 0xFF);
                        $g = (($rgb >> 8) & 0xFF);
                        $b = ($rgb & 0xFF);
                        $this->setColorHex("$r,$g,$b");
                        break;
                    case 'White':
                        $command = 'White';
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'CT':
                        $command = 'CT';
                        $msg = strval($Value);
                        $this->MQTTCommand($command, $msg);
                        break;
                }
            }

            public function setColorHex(string $color)
            {
                $command = 'Color';
                $msg = $color;
                $this->MQTTCommand($command, $msg);
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
                $this->RegisterProfileInteger('InLine.LEDSpeed', 'Speedo', '', '', 1, 20, 1);
                //Scheme Profile
                $this->RegisterProfileIntegerEx('InLine.LEDEffect', 'Shuffle', '', '', [
                    [0, 'Default',  '', -1],
                    [1, 'Wake up',  '', -1],
                    [2, 'RGB Cycle', '', -1],
                    [3, 'RBG Cycle', '', -1],
                    [4, 'Random cycle', '', -1]
                ]);
                //Color Temperature
                $this->RegisterProfileInteger('InLine.CT', 'Intensity', '', '', 158, 500, 1);
                //Online / Offline Profile
                $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
                    [false, 'Offline',  '', 0xFF0000],
                    [true, 'Online',  '', 0x00FF00]
                ]);
            }
        }
