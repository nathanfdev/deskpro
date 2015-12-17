import React from 'react';

export class ReplyForm extends React.Component {

  render() {
    return (
      <form>
        <input type="text" placeholder="Reply" />
        <button><i className="fa fa-angle-double-right"></i></button>
      </form>
    );
  }
}
