<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/BufferHelper.php';

    class InLinePlug extends IPSModule
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
            $this->RegisterPropertyString('DeviceType', 'Plug');

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
            $this->BufferResponse = '';

            $this->RegisterVariableBoolean('State', $this->Translate('State'), '~Switch', 0);

            $this->RegisterVariableFloat('EnergyPower', $this->Translate('Power'), '~Watt.3680', 1);
            $this->RegisterVariableFloat('EnergyTotal', $this->Translate('Total'), '~Electricity', 2);
            $this->RegisterVariableFloat('EnergyToday', $this->Translate('Today'), '~Electricity', 3);
            $this->RegisterVariableFloat('EnergyYesterday', $this->Translate('Yesterday'), '~Electricity', 4);
            $this->RegisterVariableFloat('EnergyCurrent', $this->Translate('Current'), '~Ampere', 5);
            $this->RegisterVariableFloat('EnergyVoltage', $this->Translate('Voltage'), '~Volt', 6);
            $this->RegisterVariableFloat('EnergyFactor', $this->Translate('Factor'), '', 7);
            $this->RegisterVariableFloat('EnergyApparentPower', $this->Translate('ApparentPower'), '', 8);
            $this->RegisterVariableFloat('EnergyReactivePower', $this->Translate('ReactivePower'), '', 9);

            $this->MaintainVariable('LEDState', $this->Translate('LED State'), 0, '~Switch', 10, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->MaintainVariable('LEDColor', $this->Translate('LED Color'), 1, '~HexColor', 11, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->MaintainVariable('LEDFade', $this->Translate('LED Fade'), 0, '~Switch', 12, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->MaintainVariable('LEDEffect', $this->Translate('LED Effect'), 1, 'InLine.LEDEffect', 13, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->MaintainVariable('LEDBrightness', $this->Translate('LED Brightness'), 1, '~Intensity.100', 14, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->MaintainVariable('LEDSpeed', $this->Translate('LED Speed'), 1, 'InLine.LEDSpeed', 15, $this->ReadPropertyString('DeviceType') == 'RGB Plug');
            $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 8);

            $this->EnableAction('State');

            if ($this->ReadPropertyString('DeviceType') == 'RGB Plug') {
                $this->EnableAction('LEDState');
                $this->EnableAction('LEDColor');
                $this->EnableAction('LEDFade');
                $this->EnableAction('LEDFade');
                $this->EnableAction('LEDEffect');
                $this->EnableAction('LEDBrightness');
                $this->EnableAction('LEDSpeed');
            }
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
                if (fnmatch('*POWER*', $Buffer->Topic)) {
                    switch ($this->ReadPropertyString('DeviceType')) {
                        case 'Plug':
                            if (fnmatch('*POWER', $Buffer->Topic)) {
                                $this->SetValue('State', $this->mappingOnOffValue($Buffer->Payload));
                            }
                            break;
                        case 'RGB Plug':
                            if (fnmatch('*POWER1', $Buffer->Topic)) {
                                $this->SendDebug('RGB POWER1', $Buffer->Payload, 0);
                                $this->SetValue('State', $this->mappingOnOffValue($Buffer->Payload));
                            }
                            if (fnmatch('*POWER2', $Buffer->Topic)) {
                                $this->SendDebug('RGB POWER2', $Buffer->Payload, 0);
                                $this->SetValue('LEDState', $this->mappingOnOffValue($Buffer->Payload));
                            }
                            break;
                        default:
                            $this->LogMessage('Invalid Device Type', KL_ERROR);
                            break;
                    }
                }
                if (fnmatch('*STATE', $Buffer->Topic)) {
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'Color')) {
                        $this->SendDebug('Receive STATE: Color', $Payload->Color, 0);
                        $rgb = explode(',', $Payload->Color);
                        $color = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                        $color = ltrim($color, '#');
                        $this->SetValue('LEDColor', hexdec($color));
                    }
                    if (property_exists($Payload, 'POWER')) {
                        $this->SetValue('State', $this->mappingOnOffValue($Payload->POWER));
                    }
                    if (property_exists($Payload, 'POWER1')) {
                        $this->SetValue('State', $this->mappingOnOffValue($Payload->POWER1));
                    }
                    if (property_exists($Payload, 'POWER2')) {
                        $this->SetValue('LEDState', $this->mappingOnOffValue($Payload->POWER2));
                    }
                }
                if (fnmatch('*SENSOR*', $Buffer->Topic)) {
                    if (fnmatch('*ENERGY*', $Buffer->Payload)) {
                        $Payload = json_decode($Buffer->Payload);
                        if (property_exists($Payload, 'ENERGY')) {
                            if (property_exists($Payload->ENERGY, 'Power')) {
                                $this->SetValue('EnergyPower', $Payload->ENERGY->Power);
                            }
                            if (property_exists($Payload->ENERGY, 'Total')) {
                                $this->SetValue('EnergyTotal', $Payload->ENERGY->Total);
                            }
                            if (property_exists($Payload->ENERGY, 'Today')) {
                                $this->SetValue('EnergyToday', $Payload->ENERGY->Today);
                            }
                            if (property_exists($Payload->ENERGY, 'Yesterday')) {
                                $this->SetValue('EnergyYesterday', $Payload->ENERGY->Yesterday);
                            }
                            if (property_exists($Payload->ENERGY, 'Current')) {
                                $this->SetValue('EnergyCurrent', $Payload->ENERGY->Current);
                            }
                            if (property_exists($Payload->ENERGY, 'Voltage')) {
                                $this->SetValue('EnergyVoltage', $Payload->ENERGY->Voltage);
                            }
                            if (property_exists($Payload->ENERGY, 'Factor')) {
                                $this->SetValue('EnergyFactor', $Payload->ENERGY->Factor);
                            }
                            if (property_exists($Payload->ENERGY, 'ApparentPower')) {
                                $this->SetValue('EnergyApparentPower', $Payload->ENERGY->ApparentPower);
                            }
                            if (property_exists($Payload->ENERGY, 'ReactivePower')) {
                                $this->SetValue('EnergyReactivePower', $Payload->ENERGY->ReactivePower);
                            }
                        }
                    }
                }
                if (fnmatch('*RESULT', $Buffer->Topic)) {
                    $this->BufferResponse = $Buffer->Payload;
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'Speed')) {
                        $this->SetValue('LEDSpeed', $Payload->Speed);
                    }
                    if (property_exists($Payload, 'Scheme')) {
                        $this->SetValue('LEDEffect', $Payload->Scheme);
                    }
                    if (property_exists($Payload, 'Dimmer')) {
                        $this->SetValue('LEDBrightness', $Payload->Dimmer);
                    }
                    if (property_exists($Payload, 'Color')) {
                        $this->SendDebug('Receive Result: Color', $Payload->Color, 0);
                        $rgb = explode(',', $Payload->Color);
                        $color = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                        $this->SetValue('LEDColor', hexdec($color));
                    }
                    if (property_exists($Payload, 'Fade')) {
                        $this->SetValue('LEDFade', $this->mappingOnOffValue($Payload->Fade));
                    }
                }
            }
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'State':
                    switch ($this->ReadPropertyString('DeviceType')) {
                        case 'Plug':
                        $command = 'POWER';
                            break;
                        case 'RGB Plug':
                            $command = 'POWER1';
                            break;
                        default:
                            $this->LogMessage('Invalid DeviceType for RequestAction', KL_ERROR);
                            return;
                        break;
                    }
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDState':
                    $command = 'POWER2';
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDFade':
                    $command = 'Fade';
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDEffect':
                    $command = 'Scheme';
                    $msg = strval($Value);
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDBrightness':
                    $command = 'Dimmer';
                    $msg = strval($Value);
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDSpeed':
                    $command = 'Speed';
                    $msg = strval($Value);
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'LEDColor':
                    $rgb = $Value;
                    $r = (($rgb >> 16) & 0xFF);
                    $g = (($rgb >> 8) & 0xFF);
                    $b = ($rgb & 0xFF);
                    $this->setColorHex("$r,$g,$b");
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
            //Online / Offline Profile
            $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
                [false, 'Offline',  '', 0xFF0000],
                [true, 'Online',  '', 0x00FF00]
            ]);
        }
    }
