<?php

namespace com\servandserv\Bot\Infrastructure\Domain\Model;

use \com\servandserv\data\bot\Update;

class UpdateRepository 
    extends \com\servandserv\Bot\Infrastructure\Persistence\PDO\Repository
    implements \com\servandserv\Bot\Domain\Model\UpdateRepository
{

    const CREATED = 1;
    const EXECUTED = 2;
    const POSTPONED = 3;

    public function register( Update $up )
    {
        $entityId = $this->getEntityIdFromUpdate( $up );
        $chat = $up->getChat();
        $params = [
            ":entityId" => $entityId,
            ":id" => $chat->getId(),
            ":type" => $chat->getType(),
            ":context" => $chat->getContext()
        ];
        $msg = $up->getMessage();
        if( $msg && $msg->getUser() ) {
            $params[":firstName"] = $msg->getUser()->getFirstName();
            $params[":lastName"] = $msg->getUser()->getLastName();
            
            $user = [
                ":entityId" => $entityId,
                ":firstName" => $msg->getUser()->getFirstName(),
                ":lastName" => $msg->getUser()->getLastName(),
                ":middleName" => $msg->getUser()->getMiddleName(),
                ":gender" => $msg->getUser()->getGender(),
                ":locale" => $msg->getUser()->getLocale()
            ];
            
            $query = "";
            foreach( $user as $col => $val ) {
                $query .= ",`".substr( $col, 1 )."`=".$col;
            }
            $query = "INSERT INTO `nusers` SET ".substr( $query, 1 )." ON DUPLICATE KEY UPDATE ".substr( $query, 1 );
            $sth = $this->conn->prepare( $query );
            $sth->execute( $user );
        }
        if( $msg && $msg->getLocation() ) {
            $params[":latitude"] = $msg->getLocation()->getLatitude();
            $params[":longitude"] = $msg->getLocation()->getLongitude();
            
            $location = [
                ":entityId" => $entityId,
                ":latitude" => $msg->getLocation()->getLatitude(),
                ":longitude" => $msg->getLocation()->getLongitude()
            ];
            $query = "";
            foreach( $location as $col => $val ) {
                $query .= ",`".substr( $col, 1 )."`=".$col;
            }
            $query = "INSERT INTO `nlocations` SET ".substr( $query, 1 ).";";
            $sth = $this->conn->prepare( $query );
            $sth->execute( $location );
        }
        if( $msg && $msg->getContact() ) {
            $params[":phoneNumber"] = $msg->getContact()->getPhoneNumber();
            
            $contact = [
                ":entityId" => $entityId,
                ":phoneNumber" => $msg->getContact()->getPhoneNumber()
            ];
            $query = "";
            foreach( $contact as $col => $val ) {
                $query .= ",`".substr( $col, 1 )."`=".$col;
            }
            $query = "INSERT INTO `ncontacts` SET ".substr( $query, 1 )." ON DUPLICATE KEY UPDATE ".substr( $query, 1 ).";";
            $sth = $this->conn->prepare( $query );
            $sth->execute( $contact );
        }
        $query = "";
        foreach( $params as $col => $val ) {
            $query .= ",`".substr( $col, 1 )."`=".$col;
        }
        $query = "INSERT INTO `nchats` SET ".substr( $query, 1 )." ON DUPLICATE KEY UPDATE ".substr( $query, 1 );
        $sth = $this->conn->prepare( $query );
        $sth->execute( $params );
        
        $command = $up->getCommand();
        if( $command ) {
            $params = [
                ":entityId"=>$entityId,
                ":command"=>$command->getName(),
                ":alias"=>$command->getAlias(),
                ":arguments"=>$command->getArguments()
            ];
            $query = "";
            foreach( $params as $col => $val ) {
                $query .= ",`".substr( $col, 1 )."`=".$col;
            }
            $query = "INSERT INTO `ncommands` SET ".substr( $query, 1 ).";";
            $sth = $this->conn->prepare( $query );
            $sth->execute( $params );
        }
        
        $params = [
            ":entityId" => $entityId,
            ":context" => $chat->getContext(),
            ":update" => $up->toXmlStr(),
            ":status" => self::CREATED
        ];
        $query = "";
        foreach( $params as $col => $val ) {
            $query .= ",`".substr( $col, 1 )."`=".$col;
        }
        $query = "INSERT INTO `nupdates` SET ".substr( $query, 1 ).";";
        $sth = $this->conn->prepare( $query );
        $sth->execute( $params );
        
        return $entityId;
    }
    
    public function archive( $autoid, Update $up )
    {
        $params = [ 
            "autoid" => $autoid,
            "status" => $up->getStatus(),
            "xmlstr" => $up->toXmlStr()
        ];
        $query = "UPDATE `nupdates` SET `status`=:status, `update`=:xmlstr WHERE `autoid`=:autoid;";
        $sth = $this->conn->prepare( $query );
        $sth->execute( $params );
    }
    
    public function findAllActive()
    {
        $updates = [];
        $params = [ "status" => self::CREATED ];
        $query = "SELECT `autoid`, `update` FROM `nupdates` WHERE `status`=:status;";
        $sth = $this->conn->prepare( $query );
        $sth->execute( $params );
        while( $row = $sth->fetch() ) {
            $up = ( new Update() )->fromXmlStr( $row["update"] );
            $updates[$row["autoid"]] = $up;
        }
        
        return $updates;
    }
}