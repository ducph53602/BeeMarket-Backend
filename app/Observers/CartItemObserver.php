<?php

namespace App\Observers;

use App\Models\CartItem;
use App\Models\Product;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        $product = $cartItem->product;
        if ($product) {
            $product->stock -= $cartItem->quantity;
            $product->save();
        }
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        $product = $cartItem->product;
        if ($product) {
            $oldQuantity = $cartItem->getOriginal('quantity');
            $newQuantity = $cartItem->quantity;

            $stockChange = $oldQuantity - $newQuantity; 

            $product->stock += $stockChange; 
            $product->save();
        }
    }

    /**
     * Handle the CartItem "deleted" event.
     */
    public function deleted(CartItem $cartItem): void
    {
        $product = $cartItem->product;
        if ($product) {
            $product->stock += $cartItem->quantity;
            $product->save();
        }
    }

    /**
     * Handle the CartItem "restored" event.
     */
    public function restored(CartItem $cartItem): void
    {
        //
    }

    
    /**
     * Handle the CartItem "force deleted" event.
     */
    public function forceDeleted(CartItem $cartItem): void
    {
        //
    }
}
