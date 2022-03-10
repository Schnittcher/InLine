<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/BufferHelper.php';

class InLineVentilator extends IPSModule
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
        $this->RegisterVariableBoolean('Oscillating', $this->Translate('Oscillating'), '~Switch', 1);
        $this->RegisterVariableBoolean('Nightmode', $this->Translate('Nightmode'), '~Switch', 2);

        $this->RegisterVariableInteger('Speed', $this->Translate('Speed'), 'InLine.FanSpeed', 3);
        $this->RegisterVariableInteger('Timer', $this->Translate('Timer'), 'InLine.FanTimer', 4);

        $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 5);

        $this->EnableAction('State');
        $this->EnableAction('Oscillating');
        $this->EnableAction('Nightmode');
        $this->EnableAction('Speed');
        $this->EnableAction('Timer');

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
            if (fnmatch('*RESULT', $Buffer->Topic)) {
                $this->BufferResponse = $Buffer->Payload;
            }
            if (fnmatch('*POWER*', $Buffer->Topic)) {
                if (fnmatch('*POWER1', $Buffer->Topic)) {
                    $this->SetValue('State', $this->mappingOnOffValue($Buffer->Payload));
                }
                if (fnmatch('*POWER2', $Buffer->Topic)) {
                    $this->SetValue('Oscillating', $this->mappingOnOffValue($Buffer->Payload));
                }
                if (fnmatch('*POWER3', $Buffer->Topic)) {
                    $this->SetValue('Nightmode', $this->mappingOnOffValue($Buffer->Payload));
                }
            }
            if (fnmatch('*speed*', $Buffer->Topic)) {
                $this->SetValue('Speed', $Buffer->Payload);
            }
            if (fnmatch('*timer*', $Buffer->Topic)) {
                $this->SetValue('Timer', $Buffer->Payload);
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
                case 'State':
                    $command = 'POWER1';
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'Oscillating':
                    $command = 'POWER2';
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'Nightmode':
                    $command = 'POWER3';
                    if ($Value === false) {
                        $msg = 'OFF';
                    } elseif ($Value === true) {
                        $msg = 'ON';
                    }
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'Speed':
                    $command = 'event';
                    $msg = 'speed=' . strval($Value);
                    $this->MQTTCommand($command, $msg);
                    break;
                case 'Timer':
                    $command = 'event';
                    $msg = 'timer=' . strval($Value);
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
        $this->RegisterProfileIntegerEx('InLine.FanSpeed', 'Speedo', '', '', [
            [0, '1',  '', -1],
            [1, '2',  '', -1],
            [2, '3', '', -1]
        ]);
        //Timer Profile
        $this->RegisterProfileIntegerEx('InLine.FanTimer', 'Clock', '', '', [
            [0, $this->Translate('Off'),  '', -1],
            [1, $this->Translate('1 Hour'),  '', -1],
            [2, $this->Translate('2 Hour'), '', -1],
            [3, $this->Translate('3 Hour'), '', -1],
            [4, $this->Translate('4 Hour'), '', -1]
        ]);
        //Online / Offline Profile
        $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
    }
}
