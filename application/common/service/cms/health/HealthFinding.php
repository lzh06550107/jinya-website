<?php

namespace app\common\service\cms\health;

final class HealthFinding
{
    private $severity;
    private $code;
    private $message;
    private $context;

    public function __construct($severity, $code, $message, array $context = [])
    {
        $severity = strtoupper((string)$severity);
        $this->severity = in_array($severity, ['PASS','WARNING','ERROR'], true) ? $severity : 'ERROR';
        $this->code = (string)$code;
        $this->message = (string)$message;
        $this->context = $context;
    }

    public function severity(){return $this->severity;}
    public function toArray(){return ['severity'=>$this->severity,'code'=>$this->code,'message'=>$this->message,'context'=>$this->context];}
}
