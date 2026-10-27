<?php
declare(strict_types=1);

function solicitudes_catalogo_rrpp(): array
{
    $filas = [
        ['rrpp-y1-patogenicos', 'Y1', 'Residuos clínicos provenientes de la atención médica en hospitales, centros médicos y clínicas humanas y veterinarias.'],
        ['rrpp-y2-medicamentos-produccion', 'Y2', 'Residuos provenientes de la producción y preparación de productos farmacéuticos.'],
        ['rrpp-y3-medicamentos-vencidos', 'Y3', 'Residuos de medicamentos y drogas humanas y veterinarias.'],
        ['rrpp-y4-biocidas', 'Y4', 'Residuos provenientes de la producción, preparación y uso de biocidas y productos fitosanitarios.'],
        ['rrpp-y6-disolventes', 'Y6', 'Residuos provenientes de la producción, preparación y uso de disolventes orgánicos.'],
        ['rrpp-y8-aceite-usado', 'Y8', 'Residuos de aceites minerales no aptos para el uso al que estaban destinados.'],
        ['rrpp-y9-mezcla-agua-hidrocarburos', 'Y9', 'Mezclas y emulsiones de aceite y agua o de hidrocarburos y agua.'],
        ['rrpp-y11-asfalto', 'Y11', 'Residuos alquitranados provenientes de refinación, destilación o cualquier otro tratamiento pirolítico.'],
        ['rrpp-y12-tintas-pinturas', 'Y12', 'Residuos provenientes de la producción, preparación y uso de tintas, colorantes, pigmentos, pinturas, lacas o barnices.'],
        ['rrpp-y13-resinas-pegamentos', 'Y13', 'Residuos provenientes de la producción, preparación y uso de resinas, látex, plastificantes o colas y adhesivos.'],
        ['rrpp-y16-liquidos-radiograficos', 'Y16', 'Residuos provenientes de la producción, preparación y uso de productos químicos y materiales para fotografía.'],
        ['rrpp-y18-barros', 'Y18', 'Residuos provenientes de operaciones de eliminación de residuos industriales.'],
        ['rrpp-y21-cromo-hexavalente', 'Y21', 'Compuestos de cromo hexavalente.'], ['rrpp-legacy-y23-zinc', 'Y23', 'Compuestos de zinc.'],
        ['rrpp-legacy-y26-cadmio', 'Y26', 'Cadmio y compuestos de cadmio.'], ['rrpp-y29-luminarias', 'Y29', 'Mercurio y compuestos de mercurio.'],
        ['rrpp-legacy-y31-plomo', 'Y31', 'Plomo y compuestos de plomo.'], ['rrpp-y34-acidos', 'Y34', 'Soluciones ácidas o ácidos en forma sólida.'],
        ['rrpp-y35-bases', 'Y35', 'Soluciones básicas o bases en forma sólida.'], ['rrpp-legacy-y39-fenoles', 'Y39', 'Fenoles y compuestos fenólicos, incluidos los clorofenoles.'],
        ['rrpp-legacy-y40-eteres', 'Y40', 'Éteres.'], ['rrpp-legacy-y41-halogenados', 'Y41', 'Disolventes orgánicos halogenados.'],
        ['rrpp-y42-disolventes-no-halogenados', 'Y42', 'Disolventes orgánicos, con exclusión de los disolventes halogenados.'],
        ['rrpp-legacy-y48-materiales', 'Y48', 'Materiales desechados contaminados con residuos peligrosos.'],
        ['rrpp-y31-y34-baterias-plomo', 'Y31/Y34', 'Baterías en desuso'],
        ['rrpp-y48-y4-elementos-biocidas', 'Y48/Y4', 'Sólidos contaminados con Biocidas y Productos Fitosanitarios'],
        ['rrpp-legacy-y48-y6-solidos', 'Y48/Y6', 'Sólidos contaminados con Disolventes Orgánicos'], ['rrpp-legacy-y48-y6-tambores', 'Y48/Y6', 'Tambores/Baldes Metalicos contaminados con Disolventes Orgánicos'],
        ['rrpp-legacy-y48-y8-solidos', 'Y48/Y8', 'Sólidos contaminados con Hidrocarburo'], ['rrpp-legacy-y48-y8-y9-tierras', 'Y48/Y8-Y9', 'Tierras contaminadas'],
        ['rrpp-legacy-y48-y12-solidos', 'Y48/Y12', 'Sólidos contaminados con tintas, colorantes, pigmentos, pinturas, lacas o barnices.'],
        ['rrpp-legacy-y48-y13-solidos', 'Y48/Y13', 'Sólidos contaminados con resinas, látex, plastificantes o colas y adhesivos.'],
        ['rrpp-legacy-y48-y33-solidos', 'Y48/Y33', 'Sólidos contaminados con Cianuro'], ['rrpp-legacy-y48-y34-solidos', 'Y48/Y34', 'Sólidos contaminados con Soluciones Ácidas'],
        ['rrpp-legacy-y48-y35-solidos', 'Y48/Y35', 'Sólidos contaminados con Soluciones Básicas'], ['rrpp-legacy-y48-y42-solidos', 'Y48/Y42', 'Sólidos contaminados con Disolventes Orgánicos'],
    ];

    $catalogo = [];
    foreach ($filas as [$id, $codigoLegal, $descripcion]) {
        $catalogo[$id] = ['id' => $id, 'codigo_legal' => $codigoLegal, 'descripcion' => $descripcion];
    }
    return $catalogo;
}

function solicitudes_catalogo_rrpp_historico(): array
{
    $filas = [
        ['rrpp-y48-y2-elementos-medicamentos', 'Y48/Y2', 'Elementos contaminados con resto de medicamentos'],
        ['rrpp-y48-y4-tambores-biocidas', 'Y48/Y4', 'Tambores/baldes metálicos contaminados con biocidas'],
        ['rrpp-y48-y8-aerosoles', 'Y48/Y8', 'Aerosoles contaminados'], ['rrpp-y48-y8-elementos-hidrocarburos', 'Y48/Y8', 'Elementos contaminados con hidrocarburos (trapos, maderas, estopas, envases plásticos, EPP, etc.)'],
        ['rrpp-y48-y8-tachos-hidrocarburos', 'Y48/Y8', 'Tachos/tambores metálicos contaminados con hidrocarburo'], ['rrpp-y48-y8-y9-tierra', 'Y48/Y8-Y9', 'Tierra contaminada'],
        ['rrpp-y48-y9-elementos-agua-aceite', 'Y48/Y9', 'Elementos contaminados con agua y aceite'], ['rrpp-y48-y9-tachos-agua-hidrocarburo', 'Y48/Y9', 'Tachos/tambores metálicos contaminados con agua e hidrocarburo'],
        ['rrpp-y48-y11-elementos-asfalto', 'Y48/Y11', 'Elementos contaminados con asfalto'],
        ['rrpp-y48-y12-aerosoles', 'Y48/Y12', 'Aerosoles contaminados'], ['rrpp-y48-y12-tambores-pintura', 'Y48/Y12', 'Baldes/tambores metálicos contaminados con pintura'], ['rrpp-y48-y12-elementos-pintura', 'Y48/Y12', 'Elementos contaminados con pintura'],
        ['rrpp-y48-y13-tambores-resinas', 'Y48/Y13', 'Baldes/tambores con resinas y pegamentos'], ['rrpp-y48-y13-elementos-resinas', 'Y48/Y13', 'Elementos contaminados con resinas y pegamentos'], ['rrpp-y48-y13-tachos-hidrocarburos', 'Y48/Y13', 'Tachos contaminados con hidrocarburos'],
        ['rrpp-y48-y16-elementos-radiografico', 'Y48/Y16', 'Elementos contaminados con líquido radiográfico'], ['rrpp-y48-y18-tambores-barros-fosfatizados', 'Y48/Y18', 'Tambores/tachos metálicos contaminados con barros fosfatizados'],
        ['rrpp-y48-y33-elementos-cianuro', 'Y48/Y33', 'Elementos contaminados con cianuro inorgánico'],
        ['rrpp-y48-y34-elementos-acidos', 'Y48/Y34', 'Elementos contaminados con ácidos'], ['rrpp-y48-y34-tierras-acido', 'Y48/Y34', 'Tierras contaminadas con ácido'], ['rrpp-y48-y34-y35-elementos-acidos-bases', 'Y48/Y34-Y35', 'Elementos contaminados con soluciones ácidas y básicas'],
        ['rrpp-y48-y35-elementos-bases', 'Y48/Y35', 'Elementos contaminados con bases'], ['rrpp-y48-y35-tambores-bases', 'Y48/Y35', 'Tambores/baldes contaminados con soluciones bases'], ['rrpp-y48-y42-elementos-disolventes', 'Y48/Y42', 'Elementos contaminados con disolventes/solventes orgánicos'],
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
    $historico = solicitudes_catalogo_rrpp_historico();
    if (isset($historico[$id])) return $historico[$id];
    $compatibilidad = [
        'Y1'=>'rrpp-y1-patogenicos', 'Y2'=>'rrpp-y2-medicamentos-produccion', 'Y3'=>'rrpp-y3-medicamentos-vencidos', 'Y4'=>'rrpp-y4-biocidas', 'Y6'=>'rrpp-y6-disolventes', 'Y8'=>'rrpp-y8-aceite-usado', 'Y9'=>'rrpp-y9-mezcla-agua-hidrocarburos', 'Y11'=>'rrpp-y11-asfalto', 'Y12'=>'rrpp-y12-tintas-pinturas', 'Y13'=>'rrpp-y13-resinas-pegamentos', 'Y16'=>'rrpp-y16-liquidos-radiograficos', 'Y18'=>'rrpp-y18-barros', 'Y21'=>'rrpp-y21-cromo-hexavalente', 'Y23'=>'rrpp-legacy-y23-zinc', 'Y26'=>'rrpp-legacy-y26-cadmio', 'Y29'=>'rrpp-y29-luminarias', 'Y31'=>'rrpp-legacy-y31-plomo', 'Y31/Y34'=>'rrpp-y31-y34-baterias-plomo', 'Y34'=>'rrpp-y34-acidos', 'Y35'=>'rrpp-y35-bases', 'Y39'=>'rrpp-legacy-y39-fenoles', 'Y40'=>'rrpp-legacy-y40-eteres', 'Y41'=>'rrpp-legacy-y41-halogenados', 'Y42'=>'rrpp-y42-disolventes-no-halogenados', 'Y48'=>'rrpp-legacy-y48-materiales', 'Y48/Y4'=>'rrpp-y48-y4-tambores-biocidas', 'Y48/Y6'=>'rrpp-legacy-y48-y6-tambores', 'Y48/Y8'=>'rrpp-legacy-y48-y8-solidos', 'Y48/Y8-Y9'=>'rrpp-legacy-y48-y8-y9-tierras', 'Y48/Y12'=>'rrpp-legacy-y48-y12-solidos', 'Y48/Y13'=>'rrpp-legacy-y48-y13-solidos', 'Y48/Y33'=>'rrpp-legacy-y48-y33-solidos', 'Y48/Y34'=>'rrpp-legacy-y48-y34-solidos', 'Y48/Y35'=>'rrpp-legacy-y48-y35-solidos', 'Y48/Y42'=>'rrpp-legacy-y48-y42-solidos',
    ];
    return isset($compatibilidad[$id]) ? $catalogo[$compatibilidad[$id]] : null;
}
