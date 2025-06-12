{{--@formatter:off--}}
<x-mail::message>
# Hola {{ $name }}

Gracias por tu pago. Ahora estamos preparando tu pedido para que muy pronto esté
contigo.

Con cada compra, impulsas la investigación y el apoyo a las familias que luchan contra
el osteosarcoma.

<x-footer-mail :tags="[
'JuntosContraElOsteosarcoma'
]"/>
</x-mail::message>
