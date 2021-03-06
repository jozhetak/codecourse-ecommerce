<?php

namespace Tests\Unit\Models\Cart;

use App\Cart\Cart;
use App\Cart\Money;
use Tests\TestCase;
use App\Models\User;
use App\Models\ProductVariation;

class CartTest extends TestCase
{
    public function test_it_can_add_products_to_the_cart()
    {
        $cart = new Cart(
        	$user = factory(User::class)->create()
        );

        $product = factory(ProductVariation::class)->create();

        $cart->add([
        	[ 'id' => $product->id, 'quantity' => 1 ]
        ]);

        $this->assertCount(1, $user->fresh()->cart);
    }

    public function test_it_increases_quantity_when_adding_more_products()
    {
        $product = factory(ProductVariation::class)->create();

        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $cart->add([
            [ 'id' =>  $product->id, 'quantity' => 1 ]
        ]);

        $cart = new Cart($user->fresh());

        $cart->add([
            [ 'id' => $product->id, 'quantity' => 1 ]
        ]);

        $this->assertEquals(2, $user->fresh()->cart->first()->pivot->quantity);
    }

    public function test_it_can_update_quantities_in_the_cart()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $product = factory(ProductVariation::class)->create();

        $cart->add([
            [ 'id' =>  $product->id, 'quantity' => 1 ]
        ]);

        $cart = new Cart($user->fresh());

        $cart->update($product->id, $quantity = 2);

        $this->assertEquals($quantity, $user->fresh()->cart->first()->pivot->quantity);
    }

    public function test_it_can_delete_products_in_the_cart()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create()
        );

        $cart->delete($productVariation->id);

        $this->assertCount(0, $user->fresh()->cart);
    }

    public function test_it_can_empty_all_products_in_the_cart()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create()
        );

        $cart->empty();

        $this->assertCount(0, $user->fresh()->cart);
    }

    public function test_it_can_check_if_the_cart_is_empty_of_quantites()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create(), [
                'quantity' => 0
            ]
        );

        $this->assertTrue($cart->isEmpty());
    }

    public function test_it_returns_a_money_instance_for_the_subtotal()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $this->assertInstanceOf(Money::class, $cart->subtotal());
    }

    public function test_it_gets_the_correct_subtotal()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create([
                'price' => $price = 2000
            ]), [
                'quantity' => 1
            ]
        );

        $this->assertEquals($cart->subtotal()->amount(), $price);
    }

    public function test_it_returns_a_money_instance_for_the_total()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $this->assertInstanceOf(Money::class, $cart->total());
    }

    public function test_it_syncs_the_cart_to_update_quantites()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create(), [
                'quantity' => 1
            ]
        );

        $cart->sync();

        $this->assertEquals($user->fresh()->cart->first()->pivot->quantity, 0);
    }

    public function test_it_can_check_if_the_cart_has_changed_after_syncing()
    {
        $cart = new Cart(
            $user = factory(User::class)->create()
        );

        $user->cart()->attach(
            $productVariation = factory(ProductVariation::class)->create(), [
                'quantity' => 1
            ]
        );

        $cart->sync();

        $this->assertTrue($cart->hasChanged());
    }
}
