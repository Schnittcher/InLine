<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/BufferHelper.php';

    class InLineAromaDiffusor extends IPSModule
    {
        use VariableProfileHelper;
        use MQTTHelper;
        use BufferHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

            //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
            $this->RegisterPropertyString('Topic', '');
            $this->RegisterPropertyBoolean('MessageRetain', false);
            $this->RegisterPropertyString('FullTopic', '%prefix%/%topic%');

            $this->createVariabenProfiles();
            $this->RegisterVariableInteger('Level', $this->Translate('Atomizer Level'), 'InLineDiffusor.Level', 0);
            $this->RegisterVariableBoolean('Water', $this->Translate('Water'), 'InLineDiffusor.Water', 1);
            $this->RegisterVariableBoolean('LedPower', $this->Translate('LED Power'), '~Switch', 3);
            $this->RegisterVariableBoolean('Fade', $this->Translate('LED Fade'), '~Switch', 3);
            $this->RegisterVariableInteger('Color', $this->Translate('LED Color'), 'HexColor', 4);
            $this->RegisterVariableInteger('Brightness', $this->Translate('LED Brightness'), 'Intensity.100', 5);
            $this->RegisterVariableInteger('Effect', $this->Translate('LED Effect'), 'InLine.LEDEffect', 6);
            $this->RegisterVariableInteger('Speed', $this->Translate('LED Speed'), 'InLine.LEDSpeed', 7);
            $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 8);
            $this->EnableAction('Level');
            $this->EnableAction('LedPower');
            $this->EnableAction('Speed');
            $this->EnableAction('Fade');
            $this->EnableAction('Effect');
            $this->EnableAction('Color');
            $this->EnableAction('Brightness');
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
            $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
            //Setze Filter für ReceiveData
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
                $this->SendDebug('MSG', $Buffer->Payload, 0);
                $Payload = json_decode($Buffer->Payload);

                if (fnmatch('*LWT', $Buffer->Topic)) {
                    $this->SendDebug('State Payload', $Buffer->Payload, 0);
                    if (strtolower($Buffer->Payload) == 'online') {
                        SetValue($this->GetIDForIdent('DeviceStatus'), true);
                    } else {
                        SetValue($this->GetIDForIdent('DeviceStatus'), false);
                    }
                }
                if (fnmatch('*RESULT', $Buffer->Topic)) {
                    $this->BufferResponse = $Buffer->Payload;
                }
                if (fnmatch('*level*', $Buffer->Payload)) {
                    if (is_object($Payload)) {
                        if (property_exists($Payload, 'level')) {
                            $this->SetValue('Level', $Payload->level);
                        }
                    }
                }
                if (fnmatch('*water*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'water')) {
                        $this->SetValue('Water', $Payload->water);
                    }
                }
                if (fnmatch('*POWER*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'POWER')) {
                        $this->SendDebug('Receive Result: POWER', $Payload->POWER, 0);
                        switch ($Payload->POWER) {
                                        case 'OFF':
                                            SetValue($this->GetIDForIdent('LedPower'), 0);
                                            break;
                                        case 'ON':
                                            SetValue($this->GetIDForIdent('LedPower'), 1);
                                            break;
                                    }
                    }
                }
                if (fnmatch('*Speed*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'Speed')) {
                        $this->SendDebug('Receive Result: Speed', $Payload->Speed, 0);
                        SetValue($this->GetIDForIdent('Speed'), $Payload->Speed);
                    }
                }
                if (fnmatch('*Scheme*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'Scheme')) {
                        $this->SendDebug('Receive Result: Scheme', $Payload->Scheme, 0);
                        SetValue($this->GetIDForIdent('Effect'), $Payload->Scheme);
                    }
                }
                if (fnmatch('*Dimmer*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'Dimmer')) {
                        $this->SendDebug('Receive Result: Dimmer', $Payload->Dimmer, 0);
                        SetValue($this->GetIDForIdent('Brightness'), $Payload->Dimmer);
                    }
                }
                if (fnmatch('*Color*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'Color')) {
                        $this->SendDebug('Receive Result: Color', $Payload->Color, 0);
                        $rgb = explode(',', $Payload->Color);
                        $color = sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
                        //$color = $Payload->Color;
                        SetValue($this->GetIDForIdent('Color'), hexdec(($color)));
                    }
                }
                if (fnmatch('*Fade*', $Buffer->Payload)) {
                    if (property_exists($Payload, 'Fade')) {
                        $this->SendDebug('Receive Result: Fade', $Payload->Fade, 0);
                        if (strtoupper($Payload->Fade) == 'ON') {
                            SetValue($this->GetIDForIdent('Fade'), true);
                        } else {
                            SetValue($this->GetIDForIdent('Fade'), false);
                        }
                    }
                }
            }
        }

        public function setScheme(int $schemeID)
        {
            $command = 'Scheme';
            $msg = strval($schemeID);
            $this->MQTTCommand($command, $msg);
        }

        public function setDimmer(int $value)
        {
            $command = 'Dimmer';
            $msg = strval($value);
            $this->MQTTCommand($command, $msg);
        }

        public function setColorHex(string $color)
        {
            $command = 'Color';
            $msg = $color;
            $this->MQTTCommand($command, $msg);
        }

        public function setFade(bool $value)
        {
            $command = 'Fade';
            $msg = $value;
            if ($msg === false) {
                $msg = 'OFF';
            } elseif ($msg === true) {
                $msg = 'ON';
            }
            $this->MQTTCommand($command, $msg);
        }

        public function setSpeed(int $value)
        {
            $command = 'Speed';
            $msg = strval($value);
            $this->MQTTCommand($command, $msg);
        }

        public function setLevel(int $value)
        {
            $command = 'Script';
            $msg = '> level=' . strval($value);
            $this->MQTTCommand($command, $msg);
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                    case 'Level':
                        $this->setLevel($Value);
                        break;
                    case 'LedPower':
                        $command = 'POWER';
                        if ($Value === false) {
                            $msg = 'OFF';
                        } elseif ($Value === true) {
                            $msg = 'ON';
                        }
                        $this->MQTTCommand($command, $msg);
                        break;
                    case 'Speed':
                        $this->setSpeed($Value);
                        break;
                      case 'Fade':
                        $this->setFade($Value);
                        break;
                      case 'Effect':
                        $this->setScheme($Value);
                        break;
                      case 'Color':
                          $rgb = $Value;
                          $r = (($rgb >> 16) & 0xFF);
                          $g = (($rgb >> 8) & 0xFF);
                          $b = ($rgb & 0xFF);
                          $this->setColorHex("$r,$g,$b");
                        break;
                      case 'Brightness':
                        $this->setDimmer($Value);
                        break;
                      default:
                        // code...
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
            $this->RegisterProfileIntegerEx('InLineDiffusor.Level', 'Intensity', '', '', [
                [0, $this->Translate('Off'),  '', -1],
                [1, $this->Translate('Low'),  '', -1],
                [2, $this->Translate('Medium'), '', -1],
                [3, $this->Translate('High'), '', -1],
            ]);
            //Online / Offline Profile
            $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
                [false, 'Offline',  '', 0xFF0000],
                [true, 'Online',  '', 0x00FF00]
            ]);
            $this->RegisterProfileBooleanEx('InLineDiffusor.Water', 'Warning', '', '', [
                [false, $this->Translate('No Water'),  '', 0xFF0000],
                [true, $this->Translate('Water ok'),  '', 0x00FF00]
            ]);
        }
    }
