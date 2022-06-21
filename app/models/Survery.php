<?php

class Survery
{
    public $id;
    public $id_table;
    public $score_table;
    public $score_restarnt;
    public $score_waiter;
    public $score_chef;
    public $comments;

    public static function CreateSurvery($id_table, $score_table, $score_restarnt, $score_waiter, $score_chef, $comments) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO surveys (id_table, score_table, score_restarnt, score_waiter, score_chef, comments)
        VALUES (:id_table, :score_table, :score_restarnt, :score_waiter, :score_chef, :comments)");
        $consulta->bindValue(':id_table', $id_table, PDO::PARAM_INT);
        $consulta->bindValue(':score_table', $score_table, PDO::PARAM_INT);
        $consulta->bindValue(':score_restarnt', $score_restarnt, PDO::PARAM_INT);
        $consulta->bindValue(':score_waiter', $score_waiter, PDO::PARAM_INT);
        $consulta->bindValue(':score_chef', $score_chef, PDO::PARAM_INT);
        $consulta->bindValue(':comments', $comments, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ConstructSurvery($id, $id_table, $score_table, $score_restarnt, $score_waiter, $score_chef, $comments) {
        $survey = new Survery();
        $survey->id = $id;
        $survey->id_table = $id_table;
        $survey->score_table = $score_table;
        $survey->score_restarnt = $score_restarnt;
        $survey->score_waiter = $score_waiter;
        $survey->score_chef = $score_chef;
        $survey->comments = $comments;
        return $survey;
    }

    public static function GetAllSurvies() {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM surveys");
            $consulta->execute();
            $survies = $consulta->fetchAll(PDO::FETCH_CLASS, "Survery");
            if (is_null($survies)) {
                throw new Exception("No existen encuestas");
            }
            return $survies;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function GetMesaMejorYPeorComentario($orderBy) {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM surveys ORDER BY score_table " . $orderBy . " LIMIT 1");
            $consulta->execute();
            $survies = $consulta->fetchObject("Survery");
            if (is_null($survies)) {
                throw new Exception("No existen encuestas");
            }            
            return $survies;
        } catch (Exception $e) {
            return $e->getMessage();
        }             
     }
    
}
