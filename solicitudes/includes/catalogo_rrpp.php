<?php
declare(strict_types=1);

function solicitudes_catalogo_rrpp(): array
{
    $filas = [
        // Corrientes_Residuos.xlsx: 43 registros operativos.
        ['rrpp-y1-patogenicos', 'Y1', 'RESIDUOS PATOGÉNICOS'], ['rrpp-y11-asfalto', 'Y11', 'ASFALTO, EMULSIÓN ASFÁLTICA, MEMBRANA'],
        ['rrpp-y12-tintas-pinturas', 'Y12', 'TINTAS Y PINTURAS'], ['rrpp-y13-resinas-pegamentos', 'Y13', 'RESINAS Y PEGAMENTOS'],
        ['rrpp-y16-liquidos-radiograficos', 'Y16', 'LÍQUIDOS RADIOGRÁFICOS'], ['rrpp-y18-barros', 'Y18', 'BARROS'],
        ['rrpp-y2-medicamentos-produccion', 'Y2', 'RESIDUOS DE LA PRODUCCIÓN DE MEDICAMENTOS'], ['rrpp-y21-cromo-hexavalente', 'Y21', 'CROMO HEXAVALENTE'],
        ['rrpp-y29-luminarias', 'Y29', 'LUMINARIAS'], ['rrpp-y3-medicamentos-vencidos', 'Y3', 'DESECHOS DE MEDICAMENTOS - MEDICAMENTOS VENCIDOS'],
        ['rrpp-y31-y34-baterias-plomo', 'Y31/Y34', 'BATERÍAS PLOMO'], ['rrpp-y34-acidos', 'Y34', 'ÁCIDOS'], ['rrpp-y35-bases', 'Y35', 'BASES'],
        ['rrpp-y4-biocidas', 'Y4', 'BIOCIDAS'], ['rrpp-y42-disolventes-no-halogenados', 'Y42', 'DISOLVENTES ORGÁNICOS, CON EXCLUSIÓN DE DISOLVENTES HALOG.'],
        ['rrpp-y48-y11-elementos-asfalto', 'Y48/Y11', 'ELEMENTOS CONTAMINADOS CON ASFALTO'], ['rrpp-y48-y12-aerosoles', 'Y48/Y12', 'AEROSOLES CONTAMINADOS'],
        ['rrpp-y48-y12-tambores-pintura', 'Y48/Y12', 'BALDES/TAMBORES METÁLICOS CONTAMINADOS CON PINTURA'], ['rrpp-y48-y12-elementos-pintura', 'Y48/Y12', 'ELEMENTOS CONTAMINADOS CON PINTURA'],
        ['rrpp-y48-y13-tambores-resinas', 'Y48/Y13', 'BALDES/TAMBORES CON RESINAS Y PEGAMENTOS'], ['rrpp-y48-y13-elementos-resinas', 'Y48/Y13', 'ELEMENTOS CONTAMINADOS CON RESINAS Y PEGAMENTOS'],
        ['rrpp-y48-y13-tachos-hidrocarburos', 'Y48/Y13', 'TACHOS CONTAMINADOS CON HIDROCARBUROS'], ['rrpp-y48-y16-elementos-radiografico', 'Y48/Y16', 'ELEMENTOS CONTAMINADOS CON LÍQUIDO RADIOGRÁFICO'],
        ['rrpp-y48-y18-tambores-barros-fosfatizados', 'Y48/Y18', 'TAMBORES/TACHOS METÁLICOS CONTAMINADOS CON BARROS FOSFATIZADOS'], ['rrpp-y48-y2-elementos-medicamentos', 'Y48/Y2', 'ELEMENTOS CONTAMINADOS CON RESTO DE MEDICAMENTOS'],
        ['rrpp-y48-y33-elementos-cianuro', 'Y48/Y33', 'ELEMENTOS CONTAMINADOS CON CIANURO INORGÁNICO'], ['rrpp-y48-y34-elementos-acidos', 'Y48/Y34', 'ELEMENTOS CONTAMINADOS CON ÁCIDOS'],
        ['rrpp-y48-y34-tierras-acido', 'Y48/Y34', 'TIERRAS CONTAMINADAS CON ÁCIDO'], ['rrpp-y48-y34-y35-elementos-acidos-bases', 'Y48/Y34-Y35', 'ELEMENTOS CONTAMINADOS CON SOLUCIONES ÁCIDAS Y BÁSICAS'],
        ['rrpp-y48-y35-elementos-bases', 'Y48/Y35', 'ELEMENTOS CONTAMINADOS CON BASES'], ['rrpp-y48-y35-tambores-bases', 'Y48/Y35', 'TAMBORES/BALDES CONTAMINADOS CON SOLUCIONES BASES'],
        ['rrpp-y48-y4-elementos-biocidas', 'Y48/Y4', 'ELEMENTOS CONTAMINADOS CON BIOCIDAS Y PRODUCTOS FITOSANITARIOS'], ['rrpp-y48-y4-tambores-biocidas', 'Y48/Y4', 'TAMBORES/BALDES METÁLICOS CONTAMINADOS CON BIOCIDAS'],
        ['rrpp-y48-y42-elementos-disolventes', 'Y48/Y42', 'ELEMENTOS CONTAMINADOS CON DISOLVENTES/SOLVENTES ORGÁNICOS'], ['rrpp-y48-y8-aerosoles', 'Y48/Y8', 'AEROSOLES CONTAMINADOS'],
        ['rrpp-y48-y8-elementos-hidrocarburos', 'Y48/Y8', 'ELEMENTOS CONTAMINADOS CON HIDROCARBUROS (TRAPOS, MADERAS, ESTOPAS, ENVASES PLÁSTICOS, EPP, ETC.)'],
        ['rrpp-y48-y8-tachos-hidrocarburos', 'Y48/Y8', 'TACHOS/TAMBORES METÁLICOS CONTAMINADOS CON HIDROCARBURO'], ['rrpp-y48-y8-y9-tierra', 'Y48/Y8-Y9', 'TIERRA CONTAMINADA'],
        ['rrpp-y48-y9-elementos-agua-aceite', 'Y48/Y9', 'ELEMENTOS CONTAMINADOS CON AGUA Y ACEITE'], ['rrpp-y48-y9-tachos-agua-hidrocarburo', 'Y48/Y9', 'TACHOS/TAMBORES METÁLICOS CONTAMINADOS CON AGUA E HIDROCARBURO'],
        ['rrpp-y6-disolventes', 'Y6', 'DISOLVENTES ORGÁNICOS'], ['rrpp-y8-aceite-usado', 'Y8', 'ACEITE USADO'], ['rrpp-y9-mezcla-agua-hidrocarburos', 'Y9', 'MEZCLA DE AGUA C/HIDROCARBUROS'],
        // Categorías anteriores que no figuran en la planilla y siguen disponibles.
        ['rrpp-legacy-y23-zinc', 'Y23', 'Compuestos de zinc.'], ['rrpp-legacy-y26-cadmio', 'Y26', 'Cadmio y compuestos de cadmio.'],
        ['rrpp-legacy-y39-fenoles', 'Y39', 'Fenoles y compuestos fenólicos, incluidos los clorofenoles.'], ['rrpp-legacy-y40-eteres', 'Y40', 'Éteres.'],
        ['rrpp-legacy-y41-halogenados', 'Y41', 'Disolventes orgánicos halogenados.'], ['rrpp-legacy-y48-materiales', 'Y48', 'Materiales desechados contaminados con residuos peligrosos.'],
        ['rrpp-legacy-y31-plomo', 'Y31', 'Plomo y compuestos de plomo.'],
        ['rrpp-legacy-y48-y6-solidos', 'Y48/Y6', 'Sólidos contaminados con Disolventes Orgánicos'], ['rrpp-legacy-y48-y6-tambores', 'Y48/Y6', 'Tambores/Baldes Metalicos contaminados con Disolventes Orgánicos'],
        ['rrpp-legacy-y48-y12-solidos', 'Y48/Y12', 'Sólidos contaminados con tintas, colorantes, pigmentos, pinturas, lacas o barnices.'], ['rrpp-legacy-y48-y13-solidos', 'Y48/Y13', 'Sólidos contaminados con resinas, látex, plastificantes o colas y adhesivos.'],
        ['rrpp-legacy-y48-y33-solidos', 'Y48/Y33', 'Sólidos contaminados con Cianuro'], ['rrpp-legacy-y48-y34-solidos', 'Y48/Y34', 'Sólidos contaminados con Soluciones Ácidas'],
        ['rrpp-legacy-y48-y35-solidos', 'Y48/Y35', 'Sólidos contaminados con Soluciones Básicas'], ['rrpp-legacy-y48-y42-solidos', 'Y48/Y42', 'Sólidos contaminados con Disolventes Orgánicos'],
        ['rrpp-legacy-y48-y8-solidos', 'Y48/Y8', 'Sólidos contaminados con Hidrocarburo'], ['rrpp-legacy-y48-y8-y9-tierras', 'Y48/Y8-Y9', 'Tierras contaminadas'],
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
