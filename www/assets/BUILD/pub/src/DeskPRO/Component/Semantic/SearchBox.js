import React, { PropTypes } from 'react';

class SearchBox extends React.Component {
  static propTypes = {
    placeholder: PropTypes.string,
    text:        PropTypes.string,
    onUserInput: PropTypes.func
  };
  static defaultProps = {
    onUserInput() {}
  };
  constructor() {
    super();
    this.handleChange = this.handleChange.bind(this);
  }

  handleChange() {
    this.props.onUserInput(
      this.refs.textInput.value
    );
  }

  render() {
    const { placeholder, text } = this.props;
    return (<div className="item search-box">
      <div className="ui input left icon search">
        <i className="search icon" />
        <input
          type="search"
          placeholder={placeholder}
          ref="textInput"
          onChange={this.handleChange}
          value={text}
        />
      </div>
    </div>);
  }
}
export default SearchBox;
