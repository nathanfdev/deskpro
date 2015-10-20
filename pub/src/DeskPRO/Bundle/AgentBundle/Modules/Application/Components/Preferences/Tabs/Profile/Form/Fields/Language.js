import React, { PropTypes } from 'react';

export class Language extends React.Component {

  static propTypes = {
    languages: PropTypes.object.isRequired,
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    const { languages } = this.props;

    return (
      <div className="bucket-column">
        <a href="#" className="select">English <i className="fa fa-caret-down"></i></a>
        <select>
          {languages.map(language => <option key={language.get('id')} value={language.get('id')}>{language.get('title')}</option>)}
        </select>
      </div>
    );
  }
}
