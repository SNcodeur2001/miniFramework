<?php

namespace App\Core\Abstract;
use  App\Core\Session;
abstract class AbstractController
{
    protected string $layout = 'base.layout.php';
    protected Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    abstract public function index();

    abstract public function store();

    abstract public function create();


    abstract public function destroy();

    abstract public function show();

    abstract public function edit();



    protected function renderHtml(string $view, array $params = [])
    {
        extract($params);

        ob_start();
        require_once '../templates/' . $view;
        $contentForLayout = ob_get_clean();

        require_once '../templates/layout/' . $this->layout;
    }









}
