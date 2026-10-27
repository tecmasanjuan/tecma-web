<?php
declare(strict_types=1);

function solicitudes_catalogo_rrpp(): array
{
    $filas = [
        // Corrientes_Residuos.xlsx: 43 registros operativos.
        ['rrpp-y1-patogenicos', 'Y1', 'Residuos patogénicos'], ['rrpp-y2-medicamentos-produccion', 'Y2', 'Residuos de la producción de medicamentos'],
        ['rrpp-y3-medicamentos-vencidos', 'Y3', 'Desechos de medicamentos - medicamentos vencidos'], ['rrpp-y4-biocidas', 'Y4', 'Biocidas'],
        ['rrpp-y6-disolventes', 'Y6', 'Disolventes orgánicos'], ['rrpp-y8-aceite-usado', 'Y8', 'Aceite usado'],
        ['rrpp-y9-mezcla-agua-hidrocarburos', 'Y9', 'Mezcla de agua c/hidrocarburos'], ['rrpp-y11-asfalto', 'Y11', 'Asfalto, emulsión asfáltica, membrana'],
        ['rrpp-y12-tintas-pinturas', 'Y12', 'Tintas y pinturas'], ['rrpp-y13-resinas-pegamentos', 'Y13', 'Resinas y pegamentos'],
        ['rrpp-y16-liquidos-radiograficos', 'Y16', 'Líquidos radiográficos'], ['rrpp-y18-barros', 'Y18', 'Barros'],
        ['rrpp-y21-cromo-hexavalente', 'Y21', 'Cromo hexavalente'], ['rrpp-legacy-y23-zinc', 'Y23', 'Compuestos de zinc.'],
        ['rrpp-legacy-y26-cadmio', 'Y26', 'Cadmio y compuestos de cadmio.'], ['rrpp-y29-luminarias', 'Y29', 'Luminarias'],
        ['rrpp-legacy-y31-plomo', 'Y31', 'Plomo y compuestos de plomo.'], ['rrpp-y34-acidos', 'Y34', 'Ácidos'],
        ['rrpp-y35-bases', 'Y35', 'Bases'], ['rrpp-legacy-y39-fenoles', 'Y39', 'Fenoles y compuestos fenólicos, incluidos los clorofenoles.'],
        ['rrpp-legacy-y40-eteres', 'Y40', 'Éteres.'], ['rrpp-legacy-y41-halogenados', 'Y41', 'Disolventes orgánicos halogenados.'],
        ['rrpp-y42-disolventes-no-halogenados', 'Y42', 'Disolventes orgánicos, con exclusión de disolventes HALOG.'], ['rrpp-legacy-y48-materiales', 'Y48', 'Materiales desechados contaminados con residuos peligrosos.'],
        ['rrpp-y31-y34-baterias-plomo', 'Y31/Y34', 'Baterías plomo'],
        ['rrpp-y48-y2-elementos-medicamentos', 'Y48/Y2', 'Elementos contaminados con resto de medicamentos'],
        ['rrpp-y48-y4-elementos-biocidas', 'Y48/Y4', 'Elementos contaminados con biocidas y productos fitosanitarios'], ['rrpp-y48-y4-tambores-biocidas', 'Y48/Y4', 'Tambores/baldes metálicos contaminados con biocidas'],
        ['rrpp-legacy-y48-y6-solidos', 'Y48/Y6', 'Sólidos contaminados con disolventes orgánicos'], ['rrpp-legacy-y48-y6-tambores', 'Y48/Y6', 'Tambores/baldes metalicos contaminados con disolventes orgánicos'],
        ['rrpp-y48-y8-aerosoles', 'Y48/Y8', 'Aerosoles contaminados'], ['rrpp-y48-y8-elementos-hidrocarburos', 'Y48/Y8', 'Elementos contaminados con hidrocarburos (trapos, maderas, estopas, envases plásticos, EPP, etc.)'],
        ['rrpp-y48-y8-tachos-hidrocarburos', 'Y48/Y8', 'Tachos/tambores metálicos contaminados con hidrocarburo'], ['rrpp-legacy-y48-y8-solidos', 'Y48/Y8', 'Sólidos contaminados con hidrocarburo'],
        ['rrpp-y48-y8-y9-tierra', 'Y48/Y8-Y9', 'Tierra contaminada'], ['rrpp-legacy-y48-y8-y9-tierras', 'Y48/Y8-Y9', 'Tierras contaminadas'],
        ['rrpp-y48-y9-elementos-agua-aceite', 'Y48/Y9', 'Elementos contaminados con agua y aceite'], ['rrpp-y48-y9-tachos-agua-hidrocarburo', 'Y48/Y9', 'Tachos/tambores metálicos contaminados con agua e hidrocarburo'],
        ['rrpp-y48-y11-elementos-asfalto', 'Y48/Y11', 'Elementos contaminados con asfalto'],
        ['rrpp-y48-y12-aerosoles', 'Y48/Y12', 'Aerosoles contaminados'], ['rrpp-y48-y12-tambores-pintura', 'Y48/Y12', 'Baldes/tambores metálicos contaminados con pintura'],
        ['rrpp-y48-y12-elementos-pintura', 'Y48/Y12', 'Elementos contaminados con pintura'], ['rrpp-legacy-y48-y12-solidos', 'Y48/Y12', 'Sólidos contaminados con tintas, colorantes, pigmentos, pinturas, lacas o barnices.'],
        ['rrpp-y48-y13-tambores-resinas', 'Y48/Y13', 'Baldes/tambores con resinas y pegamentos'], ['rrpp-y48-y13-elementos-resinas', 'Y48/Y13', 'Elementos contaminados con resinas y pegamentos'],
        ['rrpp-y48-y13-tachos-hidrocarburos', 'Y48/Y13', 'Tachos contaminados con hidrocarburos'], ['rrpp-legacy-y48-y13-solidos', 'Y48/Y13', 'Sólidos contaminados con resinas, látex, plastificantes o colas y adhesivos.'],
        ['rrpp-y48-y16-elementos-radiografico', 'Y48/Y16', 'Elementos contaminados con líquido radiográfico'],
        ['rrpp-y48-y18-tambores-barros-fosfatizados', 'Y48/Y18', 'Tambores/tachos metálicos contaminados con barros fosfatizados'],
        ['rrpp-y48-y33-elementos-cianuro', 'Y48/Y33', 'Elementos contaminados con cianuro inorgánico'], ['rrpp-legacy-y48-y33-solidos', 'Y48/Y33', 'Sólidos contaminados con cianuro'],
        ['rrpp-y48-y34-elementos-acidos', 'Y48/Y34', 'Elementos contaminados con ácidos'], ['rrpp-y48-y34-tierras-acido', 'Y48/Y34', 'Tierras contaminadas con ácido'],
        ['rrpp-legacy-y48-y34-solidos', 'Y48/Y34', 'Sólidos contaminados con soluciones ácidas'], ['rrpp-y48-y34-y35-elementos-acidos-bases', 'Y48/Y34-Y35', 'Elementos contaminados con soluciones ácidas y básicas'],
        ['rrpp-y48-y35-elementos-bases', 'Y48/Y35', 'Elementos contaminados con bases'], ['rrpp-y48-y35-tambores-bases', 'Y48/Y35', 'Tambores/baldes contaminados con soluciones bases'],
        ['rrpp-legacy-y48-y35-solidos', 'Y48/Y35', 'Sólidos contaminados con soluciones básicas'],
        ['rrpp-y48-y42-elementos-disolventes', 'Y48/Y42', 'Elementos contaminados con disolventes/solventes orgánicos'], ['rrpp-legacy-y48-y42-solidos', 'Y48/Y42', 'Sólidos contaminados con disolventes orgánicos'],
    ];

    $catalogo = [];
    foreach ($filas as [$id, $codigoLegal, $descripcion]) {
        $catalogo[$id] = ['id' => $id, 'codigo_legal' => $codigoLegal, 'descripcion' => $descripcion];
    }
    return $catalogo;
}

function solicitudes_catalogo_rrpp_resolver(string $id): ?array
{
    $catalogo = solicitudes_catalogo_rrpp();
    if (isset($catalogo[$id])) return $catalogo[$id];
    $compatibilidad = [
        'Y1'=>'rrpp-y1-patogenicos', 'Y2'=>'rrpp-y2-medicamentos-produccion', 'Y3'=>'rrpp-y3-medicamentos-vencidos', 'Y4'=>'rrpp-y4-biocidas', 'Y6'=>'rrpp-y6-disolventes', 'Y8'=>'rrpp-y8-aceite-usado', 'Y9'=>'rrpp-y9-mezcla-agua-hidrocarburos', 'Y11'=>'rrpp-y11-asfalto', 'Y12'=>'rrpp-y12-tintas-pinturas', 'Y13'=>'rrpp-y13-resinas-pegamentos', 'Y16'=>'rrpp-y16-liquidos-radiograficos', 'Y18'=>'rrpp-y18-barros', 'Y21'=>'rrpp-y21-cromo-hexavalente', 'Y23'=>'rrpp-legacy-y23-zinc', 'Y26'=>'rrpp-legacy-y26-cadmio', 'Y29'=>'rrpp-y29-luminarias', 'Y31'=>'rrpp-legacy-y31-plomo', 'Y31/Y34'=>'rrpp-y31-y34-baterias-plomo', 'Y34'=>'rrpp-y34-acidos', 'Y35'=>'rrpp-y35-bases', 'Y39'=>'rrpp-legacy-y39-fenoles', 'Y40'=>'rrpp-legacy-y40-eteres', 'Y41'=>'rrpp-legacy-y41-halogenados', 'Y42'=>'rrpp-y42-disolventes-no-halogenados', 'Y48'=>'rrpp-legacy-y48-materiales', 'Y48/Y4'=>'rrpp-y48-y4-tambores-biocidas', 'Y48/Y6'=>'rrpp-legacy-y48-y6-tambores', 'Y48/Y8'=>'rrpp-legacy-y48-y8-solidos', 'Y48/Y8-Y9'=>'rrpp-legacy-y48-y8-y9-tierras', 'Y48/Y12'=>'rrpp-legacy-y48-y12-solidos', 'Y48/Y13'=>'rrpp-legacy-y48-y13-solidos', 'Y48/Y33'=>'rrpp-legacy-y48-y33-solidos', 'Y48/Y34'=>'rrpp-legacy-y48-y34-solidos', 'Y48/Y35'=>'rrpp-legacy-y48-y35-solidos', 'Y48/Y42'=>'rrpp-legacy-y48-y42-solidos',
    ];
    return isset($compatibilidad[$id]) ? $catalogo[$compatibilidad[$id]] : null;
}
