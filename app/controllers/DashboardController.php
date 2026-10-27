<?php
class DashboardController extends BaseController
{
    public function index(): void
    {
        Auth::loginRequired();
        $stats = (new DefectModel())->stats();
        $title = 'Tổng quan';
        $this->render('dashboard/index', compact('title','stats'));
    }
}
