<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class InLineSteckdosenleiste extends IPSModule
{
    use VariableProfileHelper;
    use MQTTHelper;

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

        $this->RegisterVariableBoolean('State1', $this->Translate('State 1'), '~Switch', 0);
        $this->RegisterVariableBoolean('State2', $this->Translate('State 2'), '~Switch', 0);
        $this->RegisterVariableBoolean('State3', $this->Translate('State 3'), '~Switch', 0);
        $this->RegisterVariableBoolean('State4', $this->Translate('State 4 (USB)'), '~Switch', 0);
        $this->EnableAction('State1');
        $this->EnableAction('State2');
        $this->EnableAction('State3');
        $this->EnableAction('State4');

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
                $Payload = json_decode($Buffer->Payload);
                if (property_exists($Payload, 'POWER1')) {
                    $this->SetValue('State1', $this->mappingOnOffValue($Payload->POWER1));
                }
                if (property_exists($Payload, 'POWER2')) {
                    $this->SetValue('State2', $this->mappingOnOffValue($Payload->POWER2));
                }
                if (property_exists($Payload, 'POWER3')) {
                    $this->SetValue('State3', $this->mappingOnOffValue($Payload->POWER3));
                }
                if (property_exists($Payload, 'POWER4')) {
                    $this->SetValue('State4', $this->mappingOnOffValue($Payload->POWER4));
                }
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
                case 'State1':
                    $command = 'POWER1';
                    break;
                case 'State2':
                    $command = 'POWER2';
                    break;
                case 'State3':
                    $command = 'POWER3';
                    break;
                case 'State4':
                    $command = 'POWER4';
                    break;
                }
        if ($Value === false) {
            $msg = 'OFF';
        } elseif ($Value === true) {
            $msg = 'ON';
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
        //Online / Offline Profile
        $this->RegisterProfileBooleanEx('InLine.DeviceStatus', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
    }
}
