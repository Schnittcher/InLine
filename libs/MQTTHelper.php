<?php

declare(strict_types=1);

if (!function_exists('fnmatch')) {
    define('FNM_PATHNAME', 1);
    define('FNM_NOESCAPE', 2);
    define('FNM_PERIOD', 4);
    define('FNM_CASEFOLD', 16);

    function fnmatch($pattern, $string, $flags = 0)
    {
        return pcre_fnmatch($pattern, $string, $flags);
    }
}

function pcre_fnmatch($pattern, $string, $flags = 0)
{
    $modifiers = null;
    $transforms = [
        '\*'      => '.*',
        '\?'      => '.',
        '\[\!'    => '[^',
        '\['      => '[',
        '\]'      => ']',
        '\.'      => '\.',
        '\\'      => '\\\\'
    ];

    // Forward slash in string must be in pattern:
    if ($flags & FNM_PATHNAME) {
        $transforms['\*'] = '[^/]*';
    }

    // Back slash should not be escaped:
    if ($flags & FNM_NOESCAPE) {
        unset($transforms['\\']);
    }

    // Perform case insensitive match:
    if ($flags & FNM_CASEFOLD) {
        $modifiers .= 'i';
    }

    // Period at start must be the same as pattern:
    if ($flags & FNM_PERIOD) {
        if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) {
            return false;
        }
    }

    $pattern = '#^'
        . strtr(preg_quote($pattern, '#'), $transforms)
        . '$#'
        . $modifiers;

    return (boolean) preg_match($pattern, $string);
}

trait MQTTHelper
{
    public function sendMQTTCommand(string $command, string $payload)
    {
        $retain = $this->ReadPropertyBoolean('MessageRetain');
        if ($retain) {
            $retain = true;
        } else {
            $retain = false;
        }

        $this->MQTTCommand($command, $payload, $retain);
        $this->BufferResponse = '';
        $result = false;
        for ($x = 0; $x < 500; $x++) {
            if ($this->BufferResponse != '') {
                $this->SendDebug('sendMQTTCommand Response', $this->BufferResponse, 0);
                $result = $this->BufferResponse;
                break;
            }
            IPS_Sleep(10);
        }
        return $result;
    }
    protected function MQTTCommand($command, $Payload, $retain = 0)
    {
        $retain = $this->ReadPropertyBoolean('MessageRetain');
        if ($retain) {
            $retain = true;
        } else {
            $retain = false;
        }

        $retain = false; // Solange der IPS MQTT Server noch kein Retain kann

        $FullTopic = explode('/', $this->ReadPropertyString('FullTopic'));
        $PrefixIndex = array_search('%prefix%', $FullTopic);
        $TopicIndex = array_search('%topic%', $FullTopic);

        $SetCommandArr = $FullTopic;
        $index = count($SetCommandArr);

        $SetCommandArr[$PrefixIndex] = 'cmnd';
        $SetCommandArr[$TopicIndex] = $this->ReadPropertyString('Topic');
        $SetCommandArr[$index] = $command;

        $Topic = implode('/', $SetCommandArr);

        $resultServer = true;
        //MQTT Server
        $Server['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Server['PacketType'] = 3;
        $Server['QualityOfService'] = 0;
        $Server['Retain'] = false;
        $Server['Topic'] = $Topic;
        $Server['Payload'] = $Payload;
        $ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__ . 'MQTT Server', $ServerJSON, 0);
        $resultServer = @$this->SendDataToParent($ServerJSON);

        if ($resultServer === false) {
            $last_error = error_get_last();
            echo $last_error['message'];
        }
    }

    protected function FilterFullTopicReceiveData()
    {
        $FullTopic = explode('/', $this->ReadPropertyString('FullTopic'));
        $PrefixIndex = array_search('%prefix%', $FullTopic);
        $TopicIndex = array_search('%topic%', $FullTopic);

        $SetCommandArr = $FullTopic;
        $SetCommandArr[$PrefixIndex] = '.*.';
        //unset($SetCommandArr[$PrefixIndex]);
        $SetCommandArr[$TopicIndex] = $this->ReadPropertyString('Topic');
        $topic = implode('\/', $SetCommandArr);

        return $topic;
    }
}
