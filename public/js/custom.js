(() => {
  'use strict';

  const bindInteractiveMap = () => {
    const pins = document.querySelectorAll('.pin');
    const interactiveMap = document.querySelector('.interactive_map');

    if (!pins.length || !interactiveMap) {
      return;
    }

    pins.forEach((pin) => {
      pin.addEventListener('click', function (event) {
        event.preventDefault();

        const pinId = this.getAttribute('data-pin-id');
        if (pinId) {
          interactiveMap.setAttribute('data-state', pinId);
        }
      });
    });
  };

  const bindVideo = () => {
    const videoWrap = document.querySelector('.video');

    if (!videoWrap) {
      return;
    }

    videoWrap.addEventListener('click', function () {
      const state = videoWrap.getAttribute('data-state');
      const video = videoWrap.querySelector('video');

      if (!video) {
        return;
      }

      if (state === '1') {
        videoWrap.setAttribute('data-state', '2');
        video.play();
      } else {
        videoWrap.setAttribute('data-state', '1');
        video.pause();
        video.muted = false;
      }
    });
  };

  const bindBlogPerPage = () => {
    document.addEventListener('change', (event) => {
      if (!event.target || event.target.id !== 'blog-perpage') {
        return;
      }

      const url = new URL(window.location.href);
      url.searchParams.set('posts_per_page', event.target.value);
      url.searchParams.delete('paged');
      url.searchParams.delete('page');
      window.location.href = url.toString();
    });
  };

  const getFileStatusText = (input) => {
    if (!input.files || input.files.length === 0) {
      return '';
    }

    if (input.files.length === 1) {
      return input.files[0].name;
    }

    return `${input.files.length} files selected.`;
  };

  const updateFileUploadStatus = (input) => {
    const wrap = input.closest('.my-filewrap');
    if (!wrap) {
      return;
    }

    const status = wrap.querySelector('.file_upload_status');
    const browseButton = wrap.querySelector('.browse_file');
    if (!status) {
      return;
    }

    const hasFiles = !!input.files && input.files.length > 0;
    status.textContent = getFileStatusText(input);
    wrap.classList.toggle('has-file', hasFiles);

    if (browseButton) {
      browseButton.textContent = hasFiles ? 'Change File' : 'Browse File';
    }
  };

  const bindFileUploadStatus = () => {
    document.querySelectorAll('.my-filewrap input[type="file"]').forEach((input) => {
      if (input.dataset.fileUploadStatusBound === 'true') {
        updateFileUploadStatus(input);
        return;
      }

      input.dataset.fileUploadStatusBound = 'true';
      updateFileUploadStatus(input);
      input.addEventListener('change', () => updateFileUploadStatus(input));
    });
  };

  const bindGravityFormsRenderEvents = () => {
    document.addEventListener('gform_post_render', () => {
      bindFileUploadStatus();
      clearSubmitLoading();
    });
    document.addEventListener('gform/postRender', () => {
      bindFileUploadStatus();
      clearSubmitLoading();
    });

    if (window.jQuery) {
      window.jQuery(document).on('gform_post_render', () => {
        bindFileUploadStatus();
        clearSubmitLoading();
      });
    }
  };

  const setSubmitLoading = (button) => {
    if (!button || button.classList.contains('is-loading')) {
      return;
    }

    button.dataset.submitText = button.textContent.trim();
    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');
  };

  const clearSubmitLoading = () => {
    document.querySelectorAll('.custom-submit.is-loading').forEach((button) => {
      button.classList.remove('is-loading');
      button.removeAttribute('aria-busy');
      delete button.dataset.submitText;
    });
  };

  const bindSubmitLoading = () => {
    document.addEventListener('click', (event) => {
      const button = event.target.closest('.custom-submit');

      if (button) {
        setSubmitLoading(button);
      }
    });

    document.addEventListener('submit', (event) => {
      const button = event.target.querySelector('.custom-submit');

      if (button) {
        setSubmitLoading(button);
      }
    });
  };

  const bindSubmenus = () => {
    document.querySelectorAll('.submenu_dropdown-item > button').forEach((button) => {
      button.addEventListener('click', function () {
        const currentItem = this.closest('.submenu_dropdown-item');

        document.querySelectorAll('.submenu_dropdown-item.active').forEach((item) => {
          if (item !== currentItem) {
            item.classList.remove('active');
          }
        });

        if (currentItem) {
          currentItem.classList.toggle('active');
        }
      });
    });

    document.addEventListener('click', (event) => {
      if (event.target.closest('.submenu_dropdown-item')) {
        return;
      }

      document.querySelectorAll('.submenu_dropdown-item.active').forEach((item) => {
        item.classList.remove('active');
      });
    });
  };

  const bindAccordion = () => {
    const accordion = document.querySelector('#my-accordion');

    if (accordion && typeof accordion.accordionjs === 'function') {
      accordion.accordionjs({
        closeAble: true,
        closeOther: true,
        slideSpeed: 150,
        activeIndex: 100,
      });
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    bindVideo();
    bindInteractiveMap();
    bindBlogPerPage();
    bindFileUploadStatus();
    bindGravityFormsRenderEvents();
    bindSubmitLoading();
    bindSubmenus();
    bindAccordion();
  });
})();
