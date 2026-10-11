<?php
require __DIR__ . '/tests/bootstrap.php';
$input = '[et_pb_section][et_pb_row][et_pb_column type="4_4"][et_pb_text]<h1>Ace</h1>[/et_pb_text][et_pb_button button_text="Start" button_url="/start" /][/et_pb_column][/et_pb_row][/et_pb_section]';
$builder = new \SuiteEdge\Migrator\Elementor\DocumentBuilder();
$data = (new \SuiteEdge\Migrator\Elementor\Classic\Serializer())->serialize($builder->build($input));
(new \SuiteEdge\Migrator\Elementor\Validator())->validate($data);
echo json_encode(['data'=>$data,'warnings'=>$builder->warnings],JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
