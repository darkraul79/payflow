{{--@formatter:off--}}
<x-mail::message>
# Hemos recibido tu pedido: {{ $order_number }}.

Hemos recibido tu pedido.

Gracias por *subirte a la ola solidaria*. Cada compra en nuestra tienda impulsa la investigación contra el *osteosarcoma*, y nos permite acompañar mejor a las familias que lo viven.

En breve recibirás una actualización con más detalles.



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
