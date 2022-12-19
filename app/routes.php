<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");
        return $response;
    });

    // เชื่อมต่อ SQL

    // GET method
    // https://arjunphp.com/how-to-connect-to-mysql-database-in-slim-framework-4/

    $app->get('/datas', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("SELECT * FROM main_result");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    // CREATE method
    $app->post('/datas/add',function (Request $request , Response $response , array $args) {
        $data = $request->getParsedBody();
        $the_level = $data["the_level"];
        $the_comment = $data["the_comment"];
        $the_content = $data["the_content"];

        // คำสั่งเขียน SQL
        $sql = "INSERT INTO main_result (the_level , the_comment , the_content) VALUES (:the_level , :the_comment , :the_content)";

        try{
            $db = $this->get(PDO::class);

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':the_level', $the_level);
            $stmt->bindParam(':the_comment', $the_comment);
            $stmt->bindParam(':the_content', $the_content);
            // $stmt->bindParam(':the_create', $the_create);

            $result = $stmt->execute();

            $db = null;
            $response->getBody()->write(json_encode($result));

            return $response
                ->withHeader('Content-Type','application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error  = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $request
                ->withHeader('Content-Type','application/json')
                ->withStatus(500);
        }
    });

    // Update Method
    $app->put('/datas/update/{id}',function (Request $request , Response $response , array $args) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $the_level = $data["the_level"];
        $the_comment = $data["the_comment"];
        $the_content = $data["the_content"];

        $sql = "UPDATE q_result SET
        
            the_level = :the_level,
            the_comment = :the_comment,
            the_content = :the_content
            
            WHERE id = $id";
        try {
            $db = $this->get(PDO::class);
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':the_level', $the_level);
            $stmt->bindParam(':the_comment', $the_comment);
            $stmt->bindParam(':the_content', $the_content);

            $result = $stmt->execute();
            $success = array (
                "message" => 'Update data ID: '.$id.' is Successfully',

            );
            $db = null;
            echo "Update successful! ";
            $response->getBody()->write(json_encode($success));

            return $response
                ->withHeader('Content-Type','application/json')
                ->withStatus(200);

        } catch (PDOException $e) {
            $error  = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $request
                ->withHeader('Content-Type','application/json')
                ->withStatus(500);
        }

    });


    // COUNT 
    $app->get('/datas/statistics', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $sth = $db->prepare("
        SELECT the_level,
            COUNT(the_level) as the_level            
        FROM main_result
        GROUP BY the_level
        ");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

};
