{{--@formatter:off--}}
<x-mail::message>

# ¡Hola {{ $name }}!

**¡Gracias por apoyar el trabajo de la Fundación Elena Tertre!**

Te confirmamos que has activado una donación recurrente  *{{$frequency}}* por un importe de ** {{$amount}} **.

<br><br>

El siguiente cargo se realizará automáticamente el día 5 del periodo correspondiente, en tu tarjeta.
Tu compromiso es clave para seguir visibilizando el osteosarcoma y avanzar en la detección precoz.


<br><br>

Si en algún momento necesitas actualizar o cancelar tu donación, puedes escribirnos a [ayuda@fundacionelenatertre.es](mailto:ayuda@fundacionelenatertre.es)


<br><br>

Un saludo,

<x-footer-mail :tags="[
'UnidosContraElOsteosarcoma',
]"/>
</x-mail::message>
