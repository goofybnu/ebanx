<?php

namespace App\Controllers;

use App\Models\Account;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

class EbanxController extends Controller
{

    public function reset(Request $request, Response $response, $arg): Response
    {
        try {
            $truncate = Account::truncate();
            if (!$truncate) throw new Exception("Could not reset state");
            return $response->withJson('OK', 200);
        } catch (Exception $e) {
            return $response->withJson($e->getMessage(), 404);
        }
    }

    public function balance(Request $request, Response $response, $arg): Response
    {
        $account_id = $request->getParam('account_id');
        $account = Account::find($account_id);
        if (!$account) return $response->withJson(0, 404);
        $return = (float)$account->balance;
        return $response->withJson($return, 200);
    }

    public function event(Request $request, Response $response, $arg): Response
    {
        $body = $request->getParsedBody();
        $type = $body['type'];

        switch ($type) {
            case 'deposit':
                $destination = (int)$body['destination'];
                $amount = (float)$body['amount'];
                $deposit = $this->eventDeposit($destination, $amount);
                if (!$deposit) return $response->withJson(0, 404);
                $return = ['destination' => $deposit];
                break;
            case 'withdraw':
                $origin = (int)$body['origin'];
                $amount = (float)$body['amount'];
                $withdraw = $this->eventWithdraw($origin, $amount);
                if (!$withdraw) return $response->withJson(0, 404);
                $return = ['origin' => $withdraw];
                break;
            case 'transfer':
                $origin = (int)$body['origin'];
                $amount = (float)$body['amount'];
                $destination = (int)$body['destination'];
                $transfer = $this->eventTransfer($origin, $amount, $destination);
                if (!$transfer) return $response->withJson(0, 404);
                $return = ['origin' => $transfer['origin'], 'destination' => $transfer['destination']];
                break;
            default:
                return $response->withJson(0, 404);
        }
        return $response->withJson($return, 201);
    }

    private function eventDeposit(Int $destination, Float $amount)
    {
        $this->container->db::beginTransaction();
        try {
            $account = Account::find($destination);
            if ($account) $newBalance = (float)$account->balance + $amount;
            else $newBalance = $amount;
            $deposit = Account::updateOrCreate(['id' => $destination], ['balance' => $newBalance])->save();
            if (!$deposit) throw new Exception("Could not deposit to destination account!");
            $this->container->db::commit();
            return Account::find($destination);
        } catch (Exception $e) {
            $this->container->db::rollback();
            return false;
        }
    }

    private function eventWithdraw(Int $origin, Float $amount)
    {
        $this->container->db::beginTransaction();
        try {
            $account = Account::find($origin);
            if (!$account) throw new Exception("Origin account doesnt exist!");
            $balance = (float)$account->balance;
            if ($amount > $balance) throw new Exception("Insuficient founds!");
            $newBalance = $balance - $amount;
            $withdraw = Account::where('id', $origin)->update(['balance' => $newBalance]);
            if (!$withdraw) throw new Exception("Could not withdraw from origin account!");
            $this->container->db::commit();
            return Account::find($origin);
        } catch (Exception $e) {
            $this->container->db::rollback();
            return false;
        }
    }

    private function eventTransfer(Int $origin, Float $amount, Int $destination)
    {
        $this->container->db::beginTransaction();
        try {
            $originAccount = $this->eventWithdraw($origin, $amount);
            if(!$originAccount) throw new Exception(0);
            $destinationAccount = $this->eventDeposit($destination, $amount);
            return [
                'origin' => $originAccount,
                'destination' => $destinationAccount
            ];
            $this->container->db::commit();
        } catch (Exception $e) {
            $this->container->db::rollback();
            return false;
        }
    }
}
