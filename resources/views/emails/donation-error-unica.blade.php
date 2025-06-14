{{--@formatter:off--}}
<x-mail::message>

# ¡Hola {{ $name }}!

Lamentamos informarte que no hemos podido procesar tu donación debido a un problema con el pago.

Si quieres, puedes intentarlo de nuevo o contactarnos para ayudarte.

<br><br>
Tu ayuda es muy importante para seguir luchando contra el osteosarcoma.


<br><br>
Un saludo,
El equipo de la Fundación Elena Tertre

<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma',
]"/>
</x-mail::message>
