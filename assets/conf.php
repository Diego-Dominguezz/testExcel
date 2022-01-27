<?php
$_conf = new Configuracion();

class Configuracion
{
    public $semana;
    public $fecha;
    public $fechaPasada;
    public $fechaSiguiente;
    public $ano;
    public $semanaPasada;
    public $anoPasado;
    public $semanaSiguiente;
    public $anoSiguiente;
    public $produccion;

    function __construct()
    {
        $this->initCalcularSemana();
        $this->configuracionGlobal();
    }

    public function initCalcularSemana()
    {
        $this->semana = date("W", strtotime("sunday"));
        $this->ano = date("Y", strtotime("sunday"));
        $this->fecha = date("Y-m-d", strtotime("sunday"));
        $this->dia = date("d-m-y-His", strtotime("sunday"));
        $this->semanaPasada = date("W", strtotime("last sunday"));
        $this->anoPasado = date("Y", strtotime("last sunday"));
        $this->semanaSiguiente = date("W", strtotime("next sunday"));
        $this->anoSiguiente = date("Y", strtotime("next sunday"));

        $this->fechaPasada = date("Y-m-d", strtotime("last sunday"));
        $this->fechaSiguiente = date("Y-m-d", strtotime("next sunday"));
    }

    public function calcularPorSemana($semana, $ano)
    {
        $fecha = strtotime("Y" . $ano . "W" . $semana);
        $this->semana = date("W", $fecha);
        $this->ano = date("Y", $fecha);
        $this->fecha = date("Y-m-d", $fecha);

        $fecha = strtotime("Y" . $ano . "W" . sprintf("%02d", ($semana - 1)));
        $this->semanaPasada = date("W", $fecha);
        $this->anoPasado = date("Y", $fecha);
        $this->fechaPasada = date("Y-m-d", $fecha);

        $fecha = strtotime("Y" . $ano . "W" . sprintf("%02d", ($semana + 1)));
        $this->semanaSiguiente = date("W", $fecha);
        $this->anoSiguiente = date("Y", $fecha);
        $this->fechaSiguiente = date("Y-m-d", $fecha);
        if ($semana == 1) {
            $semana = date("W", strtotime(($ano - 1) . "-12-28"));
            $ano -= 1;
            $fecha = strtotime("Y" . $ano . "W" . $semana);
            $this->semanaPasada = date("W", $fecha);
            $this->anoPasado = date("Y", $fecha);
            $this->fechaPasada = date("Y-m-d", $fecha);
        }

        $ultimaSemana = date("W", strtotime(($ano - 1) . "-12-28"));
        if ($semana == $ultimaSemana) {
            $fecha = strtotime("Y" . ($ano + 1) . "W" . '01');
            $this->semanaSiguiente = date("W", $fecha);
            $this->anoSiguiente = date("Y", $fecha);
            $this->fechaSiguiente = date("Y-m-d", $fecha);
        }
    }

    public function menosUnaSemana($semana, $ano)
    {
        if ($semana == 1) {
            $semana = date("W", strtotime(($ano - 1) . "-12-28"));
            $ano -= 1;
            $this->calcularPorSemana($semana, $ano);
        } else {
            $this->calcularPorSemana(sprintf("%02d", ($semana - 1)), $ano);
        }
    }

    public function masUnaSemana($semana, $ano)
    {
        $ultimaSemana = date("W", strtotime(($ano - 1) . "-12-28"));
        if ($semana == $ultimaSemana) { // Ultima semana del año
            $semana = '01';
            $ano++;
            $this->calcularPorSemana($semana, $ano);
        } else {
            $this->calcularPorSemana(($semana - 1), $ano);
        }
    }

    private function configuracionGlobal()
    {
        $this->produccion = $_SERVER['SERVER_NAME'] != "localhost";
    }
}
