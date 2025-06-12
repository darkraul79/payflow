{{--@formatter:off--}}
<x-mail::message>
# ¡Buenas noticias, {{ $name }}!.

**Tu pedido ha salido de nuestro almacén** y pronto lo tendrás contigo.

Gracias por subirte a la ola solidaria.


<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma'
]"/>
</x-mail::message>
