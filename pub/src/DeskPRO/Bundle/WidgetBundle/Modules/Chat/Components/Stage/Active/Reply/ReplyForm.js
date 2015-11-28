import React from 'react';

export class ReplyForm extends React.Component {

  onSubmit = event => {
    event.preventDefault();
  };

  render() {
    return (
      <div>
        <form onSubmit={this.onSubmit}>
          <div className="message-container">
            <textarea placeholder="Type your message to Noelle"></textarea>
          </div>

          <button><i className="fa fa-angle-double-right"></i></button>
        </form>
      </div>
    );
  }
}
