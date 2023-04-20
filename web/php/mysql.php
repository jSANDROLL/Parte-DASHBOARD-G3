<?php
include '../../config.php';
class MySQL{
    private $oConDB = null;
    
    public function __construct(){
        global $usuarioDB, $passDB, $portDB, $ipDB, $nombreDB;

        $this->usuarioDB = $usuarioDB;
        $this->passDB = $passDB;
        $this->portDB = $portDB;
        $this->ipDB = $ipDB;
        $this->nombreDB = $nombreDB;

    }

    public function conDBPDO()
    {
        try {
            $this->oConDB = new PDO("mysql:host=" . $this->ipDB . ";port=" . $this->portDB .  ";dbname=" . $this->nombreDB, $this->usuarioDB, $this->passDB);
            return true;
        } catch (PDOException $e) {
            echo "Error al conectar a la base de datos: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function getVendidos()
    {
        $vendidos = 0;
        try {
            $strQuery = "SELECT SUM(qty) as vendidos FROM tbl_sales";
            if($this->conDBPDO()) {
                $pQuery = $this->oConDB->prepare($strQuery);
                $pQuery->execute();
                $vendidos = $pQuery->fetchColumn();
            }
        } catch (PDOException $e) {
            echo "MySQL.getVendidos: " . $e->getMessage() . "\n";
            return -1;
        }
        return $vendidos;
    }
    public function getAlmacen()
    {
        $almacen = 0;
        try {
            $strQuery = "SELECT SUM(quantity) as enAlmacen FROM tbl_products";
            if($this->conDBPDO()){
                $pQuery = $this->oConDB->prepare($strQuery);
                $pQuery->execute();
                $almacen = $pQuery->fetchColumn();
            }
        } catch (PDOException $e) {
            echo "MySQL.getAlmacen: " . $e->getMessage() . "\n";
            return -1;
        }
        return $almacen;
    }
    public function getIngresos()
    {
        $ingreso = 0;
        try {
            $strQuery = "SELECT SUM(price*qty)  as ingresos FROM tbl_sales";
            if($this->conDBPDO()){
                $pQuery = $this->oConDB->prepare($strQuery);
                $pQuery->execute();
                $ingreso = $pQuery->fetchColumn();
            }
        } catch (PDOException $e) {
            echo "MySQL.getIngresos: " . $e->getMessage() . "\n";
            return -1;
        }
        return $ingreso;
    }

    public function getDatosGrafica(){
        $jDatos='';
        $rawdata = array();
        $i = 0;
        try {
            $strQuery = "SELECT sum(price*qty) as tPrecio, SUM(qty) as tVendidos
            ,DATE_FORMAT(date, '%Y-%m-%d') as fecha FROM tbl_sales GROUP BY DATE_FORMAT(date, '%Y-%m-%d')";

            if($this->conDBPDO()){
                $pQuery = $this->oConDB->prepare($strQuery);
                $pQuery->execute();
                $pQuery->setFetchMode(PDO::FETCH_ASSOC);
                while ($producto = $pQuery->fetch()) {
                   $oGrafica = new Grafica();
                   $oGrafica->totalPrecio = $producto['tPrecio'];
                   $oGrafica->totalVendidos = $producto['tVendidos'];
                   $oGrafica->fechaVenta = $producto['fecha'];
                   $rawdata[$i] = $oGrafica;
                   $i++;
                }
                $jDatos = json_encode($rawdata);
            }
        } catch (PDOException $e) {
            echo "MySQL.getDatosGrafica: " . $e->getMessage() . "\n";
            return -1;
        }
        return $jDatos;
    }
}
class Grafica{
    public $totalVendidos = 0;
    public $totalPrecio = 0;
    public $fechaVenta = 0;
}
?>