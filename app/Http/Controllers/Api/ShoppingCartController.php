<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ShoppingCartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $cartItems = ShoppingCart::with(['medicine.category', 'medicine.subcategory'])
            ->forUser($userId)
            ->get();

        $total = ShoppingCart::getTotalForUser($userId);
        $itemCount = ShoppingCart::getItemCountForUser($userId);

        return response()->json([
            'success' => true,
            'cart_items' => $cartItems,
            'summary' => [
                'total_amount' => $total,
                'item_count' => $itemCount,
                'items_in_cart' => $cartItems->count()
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $medicineId = $request->medicine_id;
        $quantity = $request->quantity;

        // Check if medicine is active and available
        $medicine = Medicine::active()->find($medicineId);
        
        if (!$medicine) {
            return response()->json([
                'success' => false,
                'message' => 'Medicine not found or not available'
            ], 404);
        }

        // Check if enough quantity is available
        if ($medicine->quantity_available < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $medicine->quantity_available
            ], 400);
        }

        try {
            $cartItem = ShoppingCart::addToCart($userId, $medicineId, $quantity);
            $cartItem->load(['medicine.category', 'medicine.subcategory']);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'cart_item' => $cartItem,
                'cart_summary' => [
                    'total_amount' => ShoppingCart::getTotalForUser($userId),
                    'item_count' => ShoppingCart::getItemCountForUser($userId)
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user()->id;
        $cartItem = ShoppingCart::forUser($userId)->find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $newQuantity = $request->quantity;

        // Check if enough quantity is available
        if ($cartItem->medicine->quantity_available < $newQuantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $cartItem->medicine->quantity_available
            ], 400);
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        $cartItem->load(['medicine.category', 'medicine.subcategory']);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem,
            'cart_summary' => [
                'total_amount' => ShoppingCart::getTotalForUser($userId),
                'item_count' => ShoppingCart::getItemCountForUser($userId)
            ]
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->id;
        $cartItem = ShoppingCart::forUser($userId)->find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'cart_summary' => [
                'total_amount' => ShoppingCart::getTotalForUser($userId),
                'item_count' => ShoppingCart::getItemCountForUser($userId)
            ]
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        ShoppingCart::forUser($userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    public function getCartSummary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $total = ShoppingCart::getTotalForUser($userId);
        $itemCount = ShoppingCart::getItemCountForUser($userId);
        $itemsInCart = ShoppingCart::forUser($userId)->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_amount' => $total,
                'item_count' => $itemCount,
                'items_in_cart' => $itemsInCart
            ]
        ]);
    }
}
