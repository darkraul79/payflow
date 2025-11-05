{{--@formatter:off--}}
<x-mail::message>
# Hola

Adjuntamos su factura **{{ $invoice->number }}**.

Gracias por **subirte a la ola solidaria**. Cada compra en nuestra tienda impulsa la investigación contra el *osteosarcoma*, y nos permite acompañar mejor a las familias que lo viven.


<br/><br/>



<x-footer-mail :tags="[
'JuntosContraElOsteosarcoma','SúbeteALaOlaSolidaria'
]"/>
</x-mail::message>
