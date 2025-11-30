<!-- Offcanvas -->
<!-- An Alpine.js and Tailwind CSS component by https://pinemix.com -->
<div
  x-data="{
    open: false,
    mobileFullWidth: false, 

    // 'start', 'end', 'top', 'bottom'
    position: 'end',
    
    // 'xs', 'sm', 'md', 'lg', 'xl'
    size: 'md',
    
    // Set transition classes based on position
    transitionClasses: {
       'x-transition:enter-start'() {
          if (this.position === 'start') {
            return '-translate-x-full rtl:translate-x-full';
          } else if (this.position === 'end') {
            return 'translate-x-full rtl:-translate-x-full';
          } else if (this.position === 'top') {
            return '-translate-y-full';
          } else if (this.position === 'bottom') {
            return 'translate-y-full';
          }
        },
       'x-transition:leave-end'() {
          if (this.position === 'start') {
            return '-translate-x-full rtl:translate-x-full';
          } else if (this.position === 'end') {
            return 'translate-x-full rtl:-translate-x-full';
          } else if (this.position === 'top') {
            return '-translate-y-full';
          } else if (this.position === 'bottom') {
            return 'translate-y-full';
          }
        },
    },
  }"
  x-on:keydown.window="handleKeydown"
  x-trap.noscroll.inert="open"
  x-init="init()"
>
  <!-- Placeholder -->
  <div
    class="flex flex-col items-center justify-center gap-5 rounded-lg border-2 border-dashed border-zinc-200/75 bg-zinc-50 px-4 py-44 text-sm font-medium dark:border-zinc-700 dark:bg-zinc-950/25"
  >
    <!-- Offcanvas Toggle Button -->
    <button
      x-on:click="open = true"
      type="button"
      class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-800 bg-zinc-800 px-3 py-2 text-sm font-medium leading-5 text-white hover:border-zinc-900 hover:bg-zinc-900 hover:text-white focus:outline-hidden focus:ring-2 focus:ring-zinc-500/50 active:border-zinc-700 active:bg-zinc-700 dark:border-zinc-700/50 dark:bg-zinc-700/50 dark:ring-zinc-700/50 dark:hover:border-zinc-700 dark:hover:bg-zinc-700/75 dark:active:border-zinc-700/50 dark:active:bg-zinc-700/50"
    >
      Open Offcanvas
    </button>
    <!-- END Offcanvas Toggle Button -->
  </div>
  <!-- END Placeholder -->

  <!-- Offcanvas Backdrop -->
  <div
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="z-90 fixed inset-0 overflow-hidden bg-zinc-700/75 backdrop-blur-xs dark:bg-zinc-950/50"
  >
    <!-- Offcanvas Sidebar -->
    <div
      x-cloak
      x-show="open"
      x-on:click.away="open = false"
      x-bind="transitionClasses"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-end="translate-x-0 translate-y-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="translate-x-0 translate-y-0"
      role="dialog"
      aria-modal="true"
      aria-labelledby="pm-offcanvas-title"
      class="absolute flex w-full flex-col bg-white shadow-lg will-change-transform dark:bg-zinc-900 dark:text-zinc-100 dark:shadow-zinc-950"
      x-bind:class="{
        'h-dvh top-0 end-0': position === 'end',
        'h-dvh top-0 start-0': position === 'start',
        'bottom-0 start-0 end-0': position === 'top',
        'bottom-0 start-0 end-0': position === 'bottom',
        'h-64': position === 'top' || position === 'bottom',
        'sm:max-w-xs': size === 'xs' && !(position === 'top' || position === 'bottom'),
        'sm:max-w-sm': size === 'sm' && !(position === 'top' || position === 'bottom'),
        'sm:max-w-md': size === 'md' && !(position === 'top' || position === 'bottom'),
        'sm:max-w-lg': size === 'lg' && !(position === 'top' || position === 'bottom'),
        'sm:max-w-xl': size === 'xl' && !(position === 'top' || position === 'bottom'),
        'max-w-72': !mobileFullWidth && !(position === 'top' || position === 'bottom'),
      }"
    >
      <!-- Header -->
      <div
        class="flex min-h-16 flex-none items-center justify-between border-b border-zinc-100 px-5 dark:border-zinc-800 md:px-7"
      >
        <h3 id="pm-offcanvas-title" class="py-5 font-semibold">Title</h3>

        <!-- Close Button -->
        <button
          x-ref="closeButton"
          x-on:click="open = false"
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold leading-5 text-zinc-800 hover:border-zinc-300 hover:text-zinc-900 hover:shadow-xs focus:ring-zinc-300/25 active:border-zinc-200 active:shadow-none dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:text-zinc-200 dark:focus:ring-zinc-600/50 dark:active:border-zinc-700"
        >
          <svg
            class="hi-solid hi-x -mx-1 inline-block size-4"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            ></path>
          </svg>
        </button>
        <!-- END Close Button -->
      </div>
      <!-- END Header -->

      <!-- Content -->
      <div class="flex grow flex-col overflow-y-auto p-5 md:p-7">
        <!-- Placeholder -->
        <div
          class="flex h-full flex-col items-center justify-center gap-5 rounded-lg border-2 border-dashed border-zinc-200/75 bg-zinc-50 py-44 text-sm font-medium text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950/25 dark:text-zinc-600"
        ></div>
      </div>
      <!-- END Content -->
    </div>
    <!-- END Offcanvas Sidebar -->
  </div>
  <!-- END Offcanvas Backdrop -->
</div>
<!-- END Offcanvas -->
