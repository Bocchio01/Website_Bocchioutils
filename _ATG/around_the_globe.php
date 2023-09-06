<?php

require_once 'math.php';

class AroundTheGlobe
{
    public $result = [
        'azimut' => 'NaN',
        'distanza' => 'NaN',
        'inclinazione' => 'NaN',
        'lunghezza' => 'NaN',
    ];

    private $Rpo = 6356752;
    private $Req = 6378137;

    public function init($array_of_point)
    {
        $polari_0 = MathUtils::multiply(array_values($array_of_point[0]), MathUtils::pi() / 180);
        $polari_1 = MathUtils::multiply(array_values($array_of_point[1]), MathUtils::pi() / 180);

        $polari_0[0] = MathUtils::divide(MathUtils::pi(), 2) - $polari_0[0];
        $polari_1[0] = MathUtils::divide(MathUtils::pi(), 2) - $polari_1[0];

        $this->main($polari_0, $polari_1);
    }

    private function main($polari_0, $polari_1)
    {
        $cartesiane_0 = $this->terna_cartesiana($polari_0);
        $cartesiane_1 = $this->terna_cartesiana($polari_1);

        $azimut_bussola =
            180 - $this->angolo_vettori(
                $this->vettore_piano_cartesiano([0, 0, 0], [0, 0, 1], $cartesiane_0),
                $this->vettore_piano_cartesiano([0, 0, 0], $cartesiane_0, $cartesiane_1)
            );

        if ($polari_1[1] < $polari_0[1]) {
            $azimut_bussola = 360 - $azimut_bussola;
        }

        $azimut_tunnel = MathUtils::abs($this->angolo_vettori($cartesiane_0, MathUtils::subtract($cartesiane_0, $cartesiane_1)) - 90);

        $this->result['azimut'] = number_format($azimut_bussola, 2);
        $this->result['inclinazione'] = number_format($azimut_tunnel, 2);
        $this->result['lunghezza'] = number_format(MathUtils::divide(MathUtils::norm(MathUtils::subtract($cartesiane_0, $cartesiane_1)), 1000), 2);
    }

    private function terna_cartesiana($polare)
    {
        $alfa = $polare[1];
        $beta = $polare[0];

        $terna_cartesiane = [
            $this->Req * sin($alfa) * sin($beta),
            $this->Req * cos($alfa) * sin($beta),
            $this->Rpo * cos($beta)
        ];

        return $terna_cartesiane;
    }

    private function vettore_piano_cartesiano($p1, $p2, $p3)
    {
        $vettore = MathUtils::cross(MathUtils::subtract($p1, $p2), MathUtils::subtract($p1, $p3));
        $vettore = MathUtils::divide($vettore, MathUtils::norm($vettore));

        return $vettore;
    }

    private function angolo_vettori($vet_1, $vet_2)
    {
        $CosTheta = MathUtils::max(
            MathUtils::min(
                MathUtils::divide(MathUtils::dot($vet_1, $vet_2), (MathUtils::norm($vet_1) * MathUtils::norm($vet_2))),
                1
            ),
            -1
        );

        $Theta = MathUtils::divide(MathUtils::re(MathUtils::acos($CosTheta)), MathUtils::divide(MathUtils::pi(), 180));

        return $Theta;
    }
}
