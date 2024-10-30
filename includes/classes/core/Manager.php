<?php
if (!defined('ABSPATH')) exit;

class IWB_Manager {
    public function __construct() {

    }
    public function init() {

    }

    public function isLimitReached($notice=TRUE) {
        global $iwb;
        $array=$iwb->Options->getArrayWelcomeBars();
        $result=(count($array)>=IWB_BARS_LIMIT);
        if ($result && $notice) {
            $iwb->Options->pushInfoMessage('WelcomeBarsLimitReached', IWB_BARS_LIMIT, IWB_TAB_PREMIUM_URI);
        }
        return $result;
    }
    public function store(IWB_WelcomeBar $countdown) {
        global $iwb;
        $result=TRUE;
        $countdown->id=intval($countdown->id);
        $array=$iwb->Options->getArrayWelcomeBars();
        if($countdown->id<=0) {
            $max=0;
            foreach($array as $k=>$v) {
                /* @var $v IWB_WelcomeBar */
                if($k>$max) {
                    $max=$k;
                }
                if($v->key==$countdown->key) {
                    $iwb->Options->pushErrorMessage('WelcomeBarKeyAlreadyUsed'
                        , $v->key, $v->name);
                    $result=FALSE;
                }
            }
            $countdown->id=($max+1);
        }

        if($result) {
            $array[$countdown->id]=$countdown;
            $iwb->Options->setArrayWelcomeBars($array);
        }
        return $result;
    }
    public function query($id=FALSE) {
        global $iwb;
        if(is_numeric($id) && intval($id)===0) {
            return FALSE;
        }

        $array=$iwb->Options->getArrayWelcomeBars();
        if($id!==FALSE) {
            $result=(isset($array[$id]) ? $array[$id] : FALSE);
            if($result===FALSE) {
                foreach($array as $k=>$v) {
                    /* @var $v IWB_WelcomeBar */
                    if($v->key==$id) {
                        $result=$v;
                        break;
                    }
                }
            }
        } else {
            $result=$array;
        }
        return $result;
    }
    public function get($id, $new=FALSE) {
        global $iwb;
        $result=$this->query($id);
        /* @var $result IWB_WelcomeBar */
        if($result===FALSE && $new) {
            $result=$iwb->Dao->Utils->newDomainClass('WelcomeBar');
            $result->active=TRUE;
            $result->name='';
            $result->key='welcome-bar';
            $result->height=IWB_WelcomeBarConstants::HEIGHT_REGULAR;
            $result->textAlign=IWB_WelcomeBarConstants::TEXT_ALIGN_CENTER;

            $result->backgroundColor='#E74C3C';
            $result->textColor='#FFFFFF';

            $result->titleText='The Welcome Bar';
            $result->titleFontSize=22;
            $result->subtitleText='Click <a href="https://intellywp.com" target="_blank">here</a> and see what happens.';
            $result->subtitleFontSize=16;

            $result->showCloseButton=TRUE;
            $result->showCtaButton=TRUE;
            $result->ctaText='CONTINUE ››';
            $result->ctaFontSize=16;
            $result->ctaTarget=IWB_WelcomeBarConstants::CTA_TARGET_SAME_WINDOW;
        }
        return $result;
    }
    public function delete($ids) {
        global $iwb;
        $ids=$iwb->Utils->toArray($ids);
        $array=$iwb->Options->getArrayWelcomeBars();
        foreach($ids as $id) {
            unset($array[$id]);
        }
        $iwb->Options->setArrayWelcomeBars($array);
        return TRUE;
    }
    public function copy($ids) {
        global $iwb;
        $ids=$iwb->Utils->toArray($ids);
        if(count($ids)==0) {
            return FALSE;
        }

        $array=$iwb->Options->getArrayWelcomeBars();
        $result=FALSE;
        $e=FALSE;
        foreach($ids as $id) {
            $result=TRUE;
            if(isset($array[$id])) {
                $e=clone($array[$id]);
                /* @var $e IWB_WelcomeBar */
                $e->id=0;
                $e->name='Copy of '.$e->name;
                $this->store($e);
            }
        }
        if($e!==FALSE) {
            $iwb->Ui->redirectEdit($e->id);
        }
        return $result;
    }
}