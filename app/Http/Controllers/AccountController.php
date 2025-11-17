<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Helpers\Accounts\AgencyNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $user_id = Auth::id();

        $accounts = Account::where('user_id', $user_id)->get();

        return response()->json([
            'message' => 'Contas listadas com sucesso',
            'data' => $accounts,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        // $user_id = Auth::id();

        $validated = $request->validate([
            'user_id' => 'required|numeric|exists:users,id',
        ]);
        $user_id = $validated['user_id']; /** deixando escolher o id do usuario para facilitar minha vida */

        [ $agency_number, $account_number ] = AgencyNumberGenerator::generateAccount();

        $account = Account::create([
            'user_id' => $user_id,
            'agency_number' => $agency_number,
            'account_number' => $account_number,
            'balance' => 0,
        ]);

        return response()->json([
            'message' => 'Conta bancária criada com sucesso',
            'data' => $account,
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** validar se é o dono da conta solicitando */
        // $validated = $request->validate([
        //     'user_id' => 'required|numeric|exists:users,id',
        // ]);

        // $user_id = $validated['user_id'];

        // if ($user_id !== Auth::id()) {
        //     return response()->json([
        //         'message' => 'Ação não autorizada',
        //     ], 403);
        // }

        $validated = $request->validate([
            'agency_number'  => 'required|numeric',
            'account_number' => 'required|numeric',
        ]);

        $account =
            Account::where('agency_number', $validated['agency_number'])
                ->where('account_number', $validated['account_number'])
                ->first();

        if (!$account) {
            return response()->json([
                'message' => 'Conta não encontrada',
            ], 404);
        }

        $account->delete();

        return response()->json([
            'message' => 'Conta deletada com sucesso',
        ], 200);
    }
}
