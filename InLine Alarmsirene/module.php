<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/BufferHelper.php';

class InLineAlarmsirene extends IPSModule
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

        $this->RegisterVariableBoolean('Alarm', $this->Translate('Alarm'), '~Switch', 0);
        $this->RegisterVariableInteger('Playtime', $this->Translate('Playtime of Alarm'), 'InLine.AlarmTime', 0);
        $this->RegisterVariableInteger('Sound', $this->Translate('Sound'), 'InLine.AlarmSounds', 0);
        $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('Device State'), 'InLine.DeviceStatus', 8);
        $this->EnableAction('Alarm');
        $this->EnableAction('Playtime');
        $this->EnableAction('Sound');

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
            if (fnmatch('*RESULT', $Buffer->Topic)) {
                $this->BufferResponse = $Buffer->Payload;
                $Payload = json_decode($Buffer->Payload);
                if (property_exists($Payload, 'POWER')) {
                    $this->SetValue('Alarm', $this->mappingOnOffValue($Payload->POWER));
                }
                if (property_exists($Payload, 'TuyaReceived')) {
                    if (property_exists($Payload->TuyaReceived, '103')) {
                        $this->SendDebug('Value 103 (Playtime) HEX', $Payload->TuyaReceived->{'103'}->DpIdData, 0);
                        $this->SendDebug('Value 103 (Playtime) DEC', hexdec($Payload->TuyaReceived->{'103'}->DpIdData), 0);
                        $this->SetValue('Playtime', hexdec($Payload->TuyaReceived->{'103'}->DpIdData));
                    }
                }
            }
            if (fnmatch('*ringtone', $Buffer->Topic)) {
                $this->SetValue('Sound', $Buffer->Payload);
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
                        case 'Alarm':
                            $command = 'POWER';
                            if ($Value === false) {
                                $msg = 'OFF';
                            } elseif ($Value === true) {
                                $msg = 'ON';
                            }
                            break;
                        case 'Playtime':
                            $command = 'TuyaSend2';
                            $msg = '103,' . strval($Value);
                            break;
                        case 'Sound':
                            $command = 'TuyaSend4';
                            $msg = '102,' . strval($Value);
                            break;
                        }

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
        //AlarmTime
        $this->RegisterProfileInteger('InLine.AlarmTime', 'Clock', '', '', 1, 60, 1);
        //Alarm Sounds
        $this->RegisterProfileIntegerEx('InLine.AlarmSounds', 'Shuffle', '', '', [
            [0, $this->Translate('Sound') . ' 1',  '', -1],
            [1, $this->Translate('Sound') . ' 2',  '', -1],
            [2, $this->Translate('Sound') . ' 3', '', -1],
            [3, $this->Translate('Sound') . ' 4', '', -1],
            [4, $this->Translate('Sound') . ' 5', '', -1],
            [5, $this->Translate('Sound') . ' 6', '', -1],
            [6, $this->Translate('Sound') . ' 7', '', -1],
            [7, $this->Translate('Sound') . ' 8', '', -1],
            [8, $this->Translate('Sound') . ' 9', '', -1],
            [9, $this->Translate('Sound') . ' 10', '', -1]
        ]);
        //Online / Offline Profile
        $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
    }
}
