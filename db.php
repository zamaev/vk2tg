<?php

class DB
{
    private $db;

    public function __construct()
    {
        $this->db = new mysqli('localhost', 'name', 'pass', 'user');
    }

    public function getKey($tg)
    {
        $key = $this->db->query("select * from users where tg={$tg}")->fetch_assoc()['key'];
        if ($key || $this->db->query("insert into users (tg, `key`) values ({$tg}, '" .($key = md5($tg . rand())). "')")) {
            return $key;
        } else {
            return null;
        }
    }

    public function setVkByKey($vk, $key)
    {
        $tg = $this->db->query("select * from users where `key` like '{$key}'")->fetch_assoc();
        if ($tg['key']) {
            $this->db->query("update users set vk={$vk} where `key`='{$key}'");
            return $tg['tg'];
        } else {
            return null;
        }
    }

    public function getVk($tg)
    {
        return $this->db->query("select * from users where tg=" .$tg)->fetch_assoc();
    }

    public function getTg($vk)
    {
        return $this->db->query('select * from users where vk=' . $vk)->fetch_assoc();
    }

}
