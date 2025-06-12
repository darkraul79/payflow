{{--@formatter:off--}}
<x-mail::message>
# ¡Hola {{ $name }}!


Hemos **detectado un problema con tu pedido** y no hemos podido procesarlo
correctamente.

Por favor, revisa los detalles o contacta con nosotros para ayudarte a solucionarlo.

Queremos que formes parte de esta ola solidaria sin inconvenientes.

<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma'
]"/>
</x-mail::message>
