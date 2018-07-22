<?php
namespace manguto\cms\lib;

class Calendario
{

    var $dateStr;

    var $dateFormat;

    var $anoSolicitado;

    var $mesSolicitado;

    var $diaSolicitado;
    

    public function __construct($dateStr, $dateFormat = '')
    {
        $datas = new Datas($dateStr, $dateFormat);
        
        $this->anoSolicitado = $datas->getDate('Y');
        $this->mesSolicitado = $datas->getDate('m');
        $this->diaSolicitado = $datas->getDate('d');
    }

    public function obterMesSolicitadoHTML($urlTpl = '{$dia}', $destacarDia = true, $destacarSemanaDoDiaSolicitado = false, $destacarDiasDaSemanaDoDiaSolicitado = false)
    {
        // confs
        $th_style = 'witdh:100px;';
        $td_style = 'text-align:center;';
        $a_style = 'width:90%;';
        
        $return = [];
        // abertura da linha
        $return[] = "<table class='table table-hover table-sm w-50'>";
        $return[] = "<thead>";
        $return[] = "<tr>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Dom</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Seg</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Ter</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Qua</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Qui</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Sex</th>";
        $return[] = "<th scope='col' class='text-center' style='$th_style'>Sáb</th>";
        $return[] = "</tr>";
        $return[] = "</thead>";
        $return[] = "<tbody>";
        
        $ultimoDiaDoMes = Datas::getMonthNumberOfDays($this->anoSolicitado, $this->mesSolicitado);
        // deb($ultimoDiaDoMes,0);
        for ($d = 01; $d <= $ultimoDiaDoMes; $d ++) {
            
            $ddsNumero = Datas::staticGetWeekDayNumber(Datas::mktime('Ymd', $this->anoSolicitado . $this->mesSolicitado . $d));
            // deb($ddsNumero,0);
            $ddsSolicitadoNumero = Datas::staticGetWeekDayNumber(Datas::mktime('Ymd', $this->anoSolicitado . $this->mesSolicitado . $this->diaSolicitado));
            
            // semana atual?
            $subs = ($this->diaSolicitado - $d);
            if ($subs < 7 && $subs >= 0) {
                $aLinhaAtualContemODiaSolicitado = true;
            } else {
                $aLinhaAtualContemODiaSolicitado = false;
            }
            
            // controle de linha - tr
            if ($d == 01 || $ddsNumero == 0) {
                if ($aLinhaAtualContemODiaSolicitado && $destacarSemanaDoDiaSolicitado) {
                    $tr_style = 'background:#fc7; cursor: not-allowed; ';
                    $aSemanaAtualEstaAtiva = true;
                } else {
                    $tr_style = '';
                    $aSemanaAtualEstaAtiva = false;
                }
                // abertura de linha - tr
                $return[] = "<tr style='$tr_style' class='linha_dia_solicitado'>";
            }
            
            // complemento INICIAL dos dias do mes anterior! <<<<<<<<<<!
            if ($d == 1) {
                $return[] = str_repeat("<td style='$td_style'>&nbsp;</td>", $ddsNumero);
            }
            if ($d == $this->diaSolicitado) {
                $oDiaAtualEhOSolicitado = true;
            } else {
                $oDiaAtualEhOSolicitado = false;
            }
            
            // deb("$d - $semanaSolicitada - $destacarSemanaDias",0);
            if ($oDiaAtualEhOSolicitado || ($aSemanaAtualEstaAtiva && $destacarDiasDaSemanaDoDiaSolicitado)) {
                
                if ($oDiaAtualEhOSolicitado && $destacarDia) {
                    $a_class = 'btn btn-danger';
                } else {
                    $a_class = 'btn btn-warning';
                }
                
                $a_url = 'javascript:void(0)';
                $a_title = 'A programação para este dia já se encontra na tela abaixo.';
                $a_style .= ' cursor: not-allowed;';
            } else {
                $a_class = 'btn btn-outline-warning';
                $diaTemp2Dig = str_pad($d, 2, '0', STR_PAD_LEFT);
                $a_url = str_replace('{$dia}', $diaTemp2Dig, $urlTpl);
                $a_title = 'Clique para acessar este dia/semana';
                $a_style .= ' cursor: pointer;';
            }
            // [][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][][]
            $return[] = "<td style='$td_style'>";
            $return[] = "<a href='$a_url' class='$a_class' style='$a_style' title='$a_title $subs'>$d</a>";
            $return[] = "</td>";
            
            if ($d == $ultimoDiaDoMes) {
                // completa com celulas vazias relativas aos dias do mes seguinte >>>>>>>>>>>!
                $return[] = str_repeat("<td style='$td_style'>&nbsp;</td>", (6 - $ddsNumero));
                // ativa o fechamento da linha
                $fecharLinha = true;
            } else {
                $fecharLinha = false;
            }
            
            if ($ddsNumero == 6 || $fecharLinha) {
                $return[] = "</tr>";
            }
        }
        // complemento de dias do mes posterior
        
        // fechamento da linha
        $return[] = "</tbody>";
        $return[] = "</table>";
        $return = implode('', $return);
        $this->html = $return;
        
        return $this->html;
    }
}

?>