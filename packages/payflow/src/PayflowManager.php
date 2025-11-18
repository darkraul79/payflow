<?php

namespace Darkraul79\Payflow;

use Darkraul79\Payflow\Contracts\GatewayInterface;
use InvalidArgumentException;

class PayflowManager
{
    protected array $gateways = [];

    protected ?string $defaultGateway = null;

    public function __construct()
    {
        $this->defaultGateway = config('payflow.default', 'redsys');
    }

    /**
     * Register a gateway
     */
    public function extend(string $name, callable $callback): self
    {
        $this->gateways[$name] = $callback;

        return $this;
    }

    /**
     * Use Redsys gateway
     */
    public function withRedsys(): GatewayInterface
    {
        return $this->gateway('redsys');
    }

    /**
     * Get gateway instance by name
     */
    public function gateway(?string $name = null): GatewayInterface
    {
        $name = $name ?? $this->defaultGateway;

        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Gateway [{$name}] is not registered.");
        }

        $gateway = $this->gateways[$name]();

        if (! $gateway instanceof GatewayInterface) {
            throw new InvalidArgumentException("Gateway [{$name}] must implement GatewayInterface.");
        }

        return $gateway;
    }

    /**
     * Use Stripe gateway
     */
    public function withStripe(): GatewayInterface
    {
        return $this->gateway('stripe');
    }

    /**
     * Use PayPal gateway
     */
    public function withPaypal(): GatewayInterface
    {
        return $this->gateway('paypal');
    }

    /**
     * Set default gateway
     */
    public function setDefault(string $gateway): self
    {
        $this->defaultGateway = $gateway;

        return $this;
    }

    /**
     * Get all registered gateways
     */
    public function getRegisteredGateways(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Magic method to handle dynamic gateway calls
     */
    public function __call(string $method, array $parameters)
    {
        if (str_starts_with($method, 'with')) {
            $gatewayName = strtolower(substr($method, 4));

            return $this->gateway($gatewayName);
        }

        return $this->gateway()->$method(...$parameters);
    }
}
