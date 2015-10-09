import React from 'react';

export class Footer extends React.Component {

  constructor(props) {
    "use strict";
    super(props);
    this.state = {
      emojiOpened: false,
      message: ''
    };
  }

  toggleEmoji = () => {
    "use strict";
    const oldState = this.state;
    const newState = {...oldState};
    newState.emojiOpened = !oldState.emojiOpened;
    this.setState(newState);
  };

  handleChange (event) {
    const oldState = this.state;
    const newState = {...oldState};
    newState.message = event.target.value;
    this.setState(newState);
  };

  handleSubmit = () => {
    "use strict";
    if(this.state.message) {
      this.props.handleAddMessage(this.state.message);
      this.handleChange({target: {value: ''}});
    }
    this.state.message = '';
  };

  render() {
    return (
      <footer>
        <form onSubmit={this.handleSubmit}>
          <input onChange={this.handleChange.bind(this)} type="text" placeholder="Send a message" value={this.state.message}/>
          <a href="#" onClick={this.toggleEmoji} className="insert-emoticon"><span className="emoticon sprite sprite-emoticon-1"></span></a>
          <input onClick={this.handleSubmit} type="button" value="&#xf101;"/>
          {this.state.emojiOpened ? this.renderEmojiTable() : null}
        </form>
      </footer>
    );
  }

  renderEmojiTable () {
    "use strict";
    return <div className="emoticon-panel">
      <div>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-1"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-2"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-3"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-4"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-5"></span></a>
      </div>

      <div>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-6"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-7"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-8"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-9"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-10"></span></a>
      </div>

      <div>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-11"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-12"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-13"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-14"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-15"></span></a>
      </div>

      <div>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-16"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-17"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-18"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-19"></span></a>
        <a href="#" className="emoticon-link"><span className="emoticon sprite sprite-emoticon-20"></span></a>
      </div>
    </div>
  }
}