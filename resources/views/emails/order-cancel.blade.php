{{--@formatter:off--}}
<x-mail::message>
# ¡Hola {{ $name }}!


Lamentamos informarte que **tu pedido ha sido cancelado**. Si fue un error o quieres
volver a intentarlo, estamos aquí para ayudarte.

Recuerda que cada compra ayuda a seguir avanzando en la investigación contra el
osteosarcoma.
<br><br>

<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma',
]"/>
</x-mail::message>
