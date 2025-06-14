{{--@formatter:off--}}
<x-mail::message>

# ¡Hola {{ $name }}!

Hemos tenido un problema con la activación de tu alta como socio/amigo por un fallo en el pago.

No te preocupes, todavía estás a tiempo de sumarte a la ola solidaria y seguir impulsando la investigación contra el osteosarcoma.

Si quieres, puedes intentar completar el proceso de nuevo o contactarnos para ayudarte.

<br><br>
Estamos aquí para acompañarte en cada paso.

<br><br>
Un saludo,
El equipo de la Fundación Elena Tertre

<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma',
]"/>
</x-mail::message>
