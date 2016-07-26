import React, { PropTypes } from 'react';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder: PropTypes.string,
    text: PropTypes.string
  };

  handleChange() {
    this.props.onUserInput(
      this.refs.textInput.value
    );
  }
  
  render() {
    const {placeholder, text} = this.props;
    return <div className="item">
      <div className="ui input left icon search">
        <i className="search icon"/>
        <input
          type="search"
          placeholder={placeholder}
          ref="textInput"
          onChange={this.handleChange.bind(this)}
          value={text}
        />
      </div>
    </div>
  }
}
export default SearchBox;