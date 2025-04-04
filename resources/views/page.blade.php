@extends('layouts.frontend')

@section('vite')
  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@push('css')
  @vite('resources/css/frontend.css')
@endpush

@section('main')
  <section class="carta my-8">
    <div
      class="flex flex-col items-start justify-between gap-8 md:flex-row"
    >
      <img
        src="{{ asset('images/fundacion.jpg') }}"
        alt="Carta de la Fundación"
        class="max-h-40 w-full object-cover md:max-h-fit md:max-w-fit"
      />
      <div class="w-full flex-grow md:max-w-2/3">
        <h2 class="subtitle mb-1">Carta de los fundadores</h2>
        <h3 class="title mb-4">Fundación</h3>
        <div class="mt-4 gap-6 leading-5 md:columns-2">
          <p>
            Han pasado tres años desde que Elena está en el cielo y,
            aunque ya no esté físicamente, su imborrable presencia
            ha permanecido entre nosotros ayudándonos a reponernos.
          </p>

          <p>
            Ahora, de forma inexplicable, sentimos la necesidad de
            movernos y empezar a devolver toda la ayuda que nos
            dieron cuando lo necesitamos. Esta llamada solo puede
            venir de ELENA. Quiere unirnos en un generoso proyecto
            que lleve su nombre para ayudar a quienes estén pasando
            por lo mismo que ella. Porque esto es lo que nos va a
            sanar y lo que dará sentido a nuestras vidas: una vida
            más generosa en honor a ella.
          </p>

          <p>
            Así nace la Fundación Elena Tertre, este es su legado.
            Un lugar para unir a todas las personas que han conocido
            a Elena y colaborar por una causa común que es ELLA.
            Porque quienes la conocimos, sabemos que era alegría,
            amistad, familia y entrega. Porque ella siempre quiso
            ayudar, le encantaba llegar a las personas y unirlas.
          </p>
          <p>
            Su proyecto necesita nuestras manos y nuestro trabajo.
            Somos sus instrumentos y la dejaremos hacer a través de
            nosotros. Estamos creando una red maravillosa para
            recaudar fondos y AYUDAR al mayor número de personas. Y
            nuestra labor dará frutos, porque ELENA está detrás de
            todo.
          </p>

          <p>
            La Fundación Elena Tertre tiene dos fines bien
            definidos:
          </p>

          <p>
            Ayudar en la investigación del osteosarcoma para que la
            ciencia consiga encontrar una cura a este cáncer que
            muchos niños no logran superar. Ayudar a mejorar la
            calidad de vida de los niños enfermos y en las
            necesidades que tengan sus familias. Millones de
            gracias. Elena cuenta con todos. ¡Elena cuenta contigo!
          </p>

          <p>17 octubre 2022</p>
        </div>
      </div>
    </div>
  </section>

  <section class="ola">
    <div class="flex flex-col gap-10 md:flex-row md:justify-between items-stretch">
      <div class="flex flex-col min-w-2xs">
        <h2 class="subtitle block">La ola</h2>
        <h3 class="title mb-8 block">Una metáfora de la vida</h3>
        <div class="card bg-azul-sky w-full p-10 shadow-sm  flex items-center">
          <img
            src="{{ asset('images/logo-fundacion-vertical.svg') }}"
            class="mx-auto"
            alt="La ola"
          />
        </div>
      </div>
      <div class="">
        <div class="card bg-azul-swan h-full">
          <h4 class="mb-4 flex items-center gap-2">
                        <span
                          class="bg-azul-wave inline-flex h-[38px] w-[38px] items-center rounded-full p-1.5"
                        >
                            <img
                              src="{{ asset('images/icons/wave.svg') }}"
                              alt=""
                              class="mx-auto w-[19px]"
                            />
                        </span>
            La Ola
          </h4>

          <p>
            Cuando Elena estaba muy enferma decidió tatuarse una ola
            con su amiga Laura. Para ella, la ola era una metáfora
            de LA VIDA. En los momentos buenos, estás en la cresta,
            y en los malos, estás en la parte baja de la ola. Pero
            todos los momentos, buenos y malos, son pasajeros, no
            duran mucho y, por eso, hay que saber disfrutarlos,
            porque pasan muy rápido. Después de una ola, llega otra
            y luego, otra, y otra, y otra… Elena evitaba ahondar en
            ello, se limitaba a decirnos: “Todo pasa y hay que saber
            disfrutarlo, mi enfermedad también pasará”.
          </p>

          <p>
            Su padrino Juli, su tía Bea y su hermano David se
            tatuaron la ola también siguiendo su ejemplo. ¡Y Elena
            estaba feliz!
          </p>

          <p>
            A los pocos días, murió y, a partir de aquel momento,
            sus amigos empezaron a hacerse el mismo tatuaje. Se creó
            un movimiento donde la ola nos recordaba a ella y nos
            unió para siempre a Elena. La ola es la mejor manera de
            representar a su fundación.
          </p>
        </div>
      </div>
      <div class=" card bg-azul-swan h-full">

        <h4 class="mb-4 flex items-center gap-2">
                        <span
                          class="bg-azul-wave inline-flex h-[38px] w-[38px] items-center rounded-full p-1.5"
                        >
                            <img
                              src="{{ asset('images/icons/heart-people.svg') }}"
                              alt=""
                              class="mx-auto w-[24px]"
                            />
                        </span>
          El azul
        </h4>
        <p>Significa mar-cielo-infinito. Nos acerca a donde está.</p>
        <h4 class="mb-4 flex items-center gap-2">
                        <span
                          class="bg-azul-wave inline-flex h-[38px] w-[38px] items-center rounded-full p-1.5"
                        >
                            <img
                              src="{{ asset('images/icons/heart-charity.svg') }}"
                              alt=""
                              class="mx-auto w-[24px]"
                            />
                        </span>
          El logotipo
        </h4>
        <p>Lo han diseñado los hermanos Chillida, amigos de Elena. Queríamos que, además de ser bonito y cuidado,
          transmitiera el mensaje de la ola que ella nos dejó.</p>
        <h4 class="mb-4 flex items-center gap-2">
                        <span
                          class="bg-azul-wave inline-flex h-[38px] w-[38px] items-center rounded-full p-1.5"
                        >
                            <img
                              src="{{ asset('images/icons/heart-hand-outline.svg') }}"
                              alt=""
                              class="mx-auto w-[24px]"
                            />
                        </span>
          El nombre
        </h4>
        <p>El nombre
          Para nosotros, los fundadores, su familia, esta fundación es su legado. Nuestra inspiración ha venido de ella
          y no podría llevar otro nombre que no fuera el suyo.
          La palabra FUNDACIÓN está en mayúsculas porque nos hace soñar en grande, porque este proyecto nos llena de
          ilusión. Su nombre, Elena Tertre, está en minúsculas porque nos recuerda su adolescencia, nos acerca a la
          infancia y a la dulzura de los niños que son nuestro objetivo.</p>
      </div>
    </div>
    </div>
  </section>

  <section class="cancion ">
    <div class="flex flex-col items-center lg:flex-row gap-20">
      <div class="lg:w-2/5">
        <h2 class="subtitle">Canción por Elena Terte</h2>
        <h3 class="title mb-8">“El legado”</h3>
        <h4 class="title2 mb-3">Acepta y ama en los momentos más difíciles</h4>
        <p>Este es un mensaje de transformación y crecimiento personal, recordando que la vida puede cambiar en un
          instante, llevándonos de la abundancia a la nada. A través de esta pérdida o cambio, la canción invita a
          aceptar
          y amar incluso los momentos difíciles, pues de ellos se extrae aprendizaje y madurez.</p>
        <p>
          Con una actitud positiva y consciente, el dolor se convierte en luz y en una oportunidad para evolucionar.</p>
      </div>
      <div class="lg:w-3/5">
        <iframe src="https://www.youtube.com/embed/pluLPATYWUc" class="w-full aspect-video"
                title="Canción Legado Elena Tertre" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
      </div>
    </div>
  </section>

  <section class="sponsorship">
    <h2 class="subtitle">Conoce a nuestros voluntarios</h2>
    <h3 class="title">Patronato</h3>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-20 mt-8">
      @for($i = 0; $i < 5; $i++)
        <div class="flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front card">
              <img src="{{asset('images/banner.webp')}}" alt="Avatar">
              <h4 class="title2 text-black">Elena Boyé Delgado</h4>
              <span class="position">Fundadora</span>
            </div>
            <div class="flip-card-back card">
              <h4 class="title2">Elena Boyé Delgado</h4>
              <span class="position mb-6 block">Fundadora</span>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab alias doloremque enim exercitationem</p>
              <a href="#" class="btn btn-primary btn-small " title="Link">Link</a>
            </div>
          </div>
        </div>
      @endfor


    </div>
  </section>
@endsection
