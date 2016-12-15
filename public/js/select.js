$ (function () { // Run after page loads
  $ ('.chosen-select').each (function () {
    selenia.ext.select.init ($ (this));
  })

  // Add drop-up behavior to Chosen
    .on ('chosen:showing_dropdown', function (event, params) {
      var chosen_container = $ (event.target).next ('.chosen-container');
      var dropdown         = chosen_container.find ('.chosen-drop');
      var dropdown_top     = dropdown.offset ().top - $ (window).scrollTop ();
      var dropdown_height  = dropdown.height ();
      var viewport_height  = $ (window).height ();
      if (dropdown_top + dropdown_height > viewport_height)
        chosen_container.addClass ('chosen-drop-up');
    }).on ('chosen:hiding_dropdown', function (event, params) {
    $ (event.target).next ('.chosen-container').removeClass ('chosen-drop-up');
  });

  $ ('.chosen-container').add ('.search-field input').css ('width', '');
});

selenia.ext.select = {
  props: {},

  init: function (target) {
    var props = this.getProps (target);
    target.chosen ({
      placeholder_text: props.emptyLabel,
      no_results_text:  props.noResultsText
    });
    target.change (this.onChange.bind (this));
    if (props.dataUrl)
      $.get (props.dataUrl).then (this.onLoadData.bind (this, target));
    else if (props.linkedSelector)
      this.loadLinked (target);
  },

  getProps: function (target) {
    return this.props[$ (target).prop ('id')]
  },

  onChange: function (ev) {
    var target  = $ (ev.target)
      , props   = this.getProps (ev.target);
    props.value = target.val ();
    if (props.linkedSelector)
      this.loadLinked (target, !ev.type); // non-synthetic change events may trigger an auto-open
    if (props.onSelectNavigate) {
      var url = props.onSelectNavigate.replace (/@value/, props.value);
      selenia.go (url);
    }
  },

  onLoadData: function (target, data) {
    var props = this.getProps (target);
    target.val ('');
    target.empty ();
    if (props.emptySelection && !props.multiple)
      target.append (eval ($$ ('<option value=""${props.value?"":" selected"}>${props.emptyLabel}</option>')));
    if (data.forEach) {
      var template = $$ (
        '<option value="${v[props.valueField]}"${v==props.value?" selected" : ""}>${v[props.labelField]}</option>')
        , items    = '';
      data.forEach (function (v) {
        items += eval (template);
      });
      target.append (items);
    }
    if (props.value) {
      target.val (props.value);
      this.onChange ({ target: target });
    }
    target.trigger ('chosen:updated');
  },

  loadLinked: function (master, dontAutoOpen) {
    var self  = this
      , value = master.val ()
      , props = this.getProps (master)
      , slave = $ ('#' + props.linkedSelector);
    if (!slave.length) {
      console.error (eval ($$ ('linkedSelector "${props.linkedSelector}" was not found')));
      return;
    }
    if (value != null && value !== '') {
      var url = props.linkedUrl.replace (/\b@value\b/, value);
      $.get (url).then (function (data) {
        self.onLoadData (slave, data);
        if (props.autoOpenLinked && !dontAutoOpen)
          self.open (props.linkedSelector);
      });
    }
    else {
      this.getProps (slave).value = null; // clear slave selection to prevent it from reappearing later
      slave.empty ();
      slave.trigger ('chosen:updated');
    }
  },

  open: function (id) {
    $ ('#' + id.replace (/-/, '_') + '_chosen').trigger ('mousedown');
  }
};
