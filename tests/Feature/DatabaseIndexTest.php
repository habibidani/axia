<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

test('users table has is_guest index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'users' AND indexname LIKE '%is_guest%'");
    expect($indexes)->not->toBeEmpty();
});

test('runs table has user_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'runs' AND indexname LIKE '%user_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('runs table has company_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'runs' AND indexname LIKE '%company_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('todos table has run_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'todos' AND indexname LIKE '%run_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('todo_evaluations table has todo_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'todo_evaluations' AND indexname LIKE '%todo_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('todo_evaluations table has run_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'todo_evaluations' AND indexname LIKE '%run_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('missing_todos table has run_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'missing_todos' AND indexname LIKE '%run_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('webhook_presets table has user_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'webhook_presets' AND indexname LIKE '%user_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('goal_kpis table has company_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'goal_kpis' AND indexname LIKE '%company_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('goal_kpis table has goal_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'goal_kpis' AND indexname LIKE '%goal_id%'");
    expect($indexes)->not->toBeEmpty();
});

test('companies table has owner_user_id index', function () {
    $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'companies' AND indexname LIKE '%owner_user_id%'");
    expect($indexes)->not->toBeEmpty();
});
