export const config = {

  'top-bar' : [
    {
      tag: 'my-login-component',
      url: 'http://127.0.0.1:31080/html/ticket-sidebar.html',

      // The size of the component on their page
      dimensions: {width: 100, height: 50},

      // The properties they can (or must) pass down to my component

      timeout: 100000,

      props: {
        onDpMessage: {
          type: 'function',
          required: true
        }
      }
    }
  ],

  'ticket-sidebar' : [
    {
      tag: 'my-login-component',
      url: 'http://127.0.0.1:31080/html/ticket-sidebar.html',

      // The size of the component on their page
      dimensions: {width: 600, height: 200},

      // The properties they can (or must) pass down to my component

      timeout: 100000,

      props: {
        onDpMessage: {
          type: 'function',
          required: true
        }
      }
    }

  ]

};
