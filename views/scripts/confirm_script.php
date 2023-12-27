<script>
    document.querySelector(".clear_cache").addEventListener('click', function(e) {
    location.href = location.href + "&clear_cache=true";
  })
  var isChanged = false;
  var isClearSpiralSetting = false;
  var form = document.querySelector('#setting-form');
  var clear_spiral_setting_form = document.querySelector('#clear-spiral-setting-form');

  form.addEventListener('change', function(e) {
    isChanged = true;
  });

  clear_spiral_setting_form.addEventListener('submit', function(e) {
    isClearSpiralSetting = true;
    e.preventDefault();
    switchTab1(e);
  });


  const ui = {
    confirm: async (message) => createConfirm(message)
  }

  const createConfirm = (message) => {
    return new Promise((complete, failed) => {
      $('#confirmMessage').text(message)

      $('#confirmYes').off('click');
      $('#confirmNo').off('click');
      $('#confirmClose').off('click');

      $('#confirmYes').on('click', () => {
        $('.confirm').hide();
        complete(true);
      });
      $('#confirmNo').on('click', () => {
        $('.confirm').hide();
        complete(false);
      });
      $('#confirmClose').on('click', () => {
        isClearSpiralSetting = false
        $('.confirm').hide();
      });

      $('.confirm').show();
    });
  }

  /**
   * Clear Cache
   */
  function check() {
    isChanged = true;
    switchTab1();
  }

  /**
   * END Clear Cache
   */
  var tabs = document.querySelectorAll("nav > a");
  var basicConfig = document.querySelectorAll(".basic_config");

  for (var i = 0; i < basicConfig.length; i++) {
    basicConfig[i].addEventListener("change", textChange);
  }

  function textChange(event) {
    this.setAttribute("value", event.target.value);
  }

  for (var i = 0; i < tabs.length; i++) {
    tabs[i].addEventListener("click", switchTab1);
  }

  async function switchTab1(event) {
    if (isChanged) {
      let tab = event.target.getAttribute("tab");
      let href = event.target.getAttribute("href");
      event.preventDefault();

      if (tab == 2) {
        document.getElementsByName('_wp_http_referer')[0].value += '&tab=advance-settings';
      } else {
        document.getElementsByName('_wp_http_referer')[0].value = '/wordpress/wp-admin/options-general.php?page=spiral_member_login';
      }
      const confirm = await ui.confirm('変更を保存しますか?');
      if (confirm) {
        if (tab == 1) {
          document.querySelector("#setting-form").submit.click()
        } else {
          document.querySelector("#setting-form").submit.click()
        }
      } else {
        window.location.href = href;
      }
    }
    // Clear Siral Setting
    if(isClearSpiralSetting){
      var text = "<?php echo $this->translator->sml_translate('all_spiral_setting_data_will_be_reset_are_you_sure') ?>";
      const confirm = await ui.confirm(text);
      if (confirm) {
        document.frmProduct.submit();
      }else{
        isClearSpiralSetting = false;
      }
    }
  }

  $setting_message = document.querySelector(".clear_cache_button");

  if ($setting_message != null) {
    document.querySelector(".clear_cache_button").addEventListener("click", function(e) {
      e.preventDefault();
      document.getElementById("setting-error-settings_updated").classList.add('none');
    })
  }
</script>