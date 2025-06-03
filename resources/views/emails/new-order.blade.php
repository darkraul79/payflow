{{--@formatter:off--}}
<x-mail::message>
# Hemos recibido tu pedido: {{ $order_number }}.

Cuando recibamos el pago, te enviaremos un correo electrónico de confirmación.



<x-mail::table>
    |                  Producto      || Cantidad      | Total         |
    | :-------------: | :----------- | :------------: |  ------------: |
    @foreach($items as $item)
    | <img src="{{  $message->embed($item['image']) }}" alt="{{$item['name']}}" style="width: 50px; display:inline-flex; vertical-align: center"/>| {{$item['name']}} | {{$item['quantity']}}      | {!! $item['subtotal'] !!}          |
    @endforeach
</x-mail::table>
<x-mail::table>
    |               |               |
    |  -----------: | ------------: |
    |          *Subtotal*       | *{!! $subtotal !!}* |
    |          *Envio*         | *{!! $shipping_cost !!}* |
    |          *Total*         | **{!! $total !!}**    |
</x-mail::table>

Gracias,
<br />
{{ config('app.name') }}
</x-mail::message>
