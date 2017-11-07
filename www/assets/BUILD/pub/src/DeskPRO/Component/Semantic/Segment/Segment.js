import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Header } from 'DeskPRO/Component/Semantic/Common';

class Segment extends React.Component {

  static propTypes = {
    children:  PropTypes.oneOfType([PropTypes.object, PropTypes.string, PropTypes.array]).isRequired,
    className: PropTypes.string,
    header:    PropTypes.shape({
      level:   PropTypes.integer,
      content: PropTypes.string
    }),
    raised:     PropTypes.bool,
    horizontal: PropTypes.bool,
    vertical:   PropTypes.bool,
    piled:      PropTypes.bool,
    stacked:    PropTypes.bool,
    disabled:   PropTypes.bool,
    loading:    PropTypes.bool
  };

  static defaultProps = {
    className: '',
    header:    {
      size:    3,
      content: ''
    },
    raised:     false,
    horizontal: false,
    vertical:   false,
    piled:      false,
    stacked:    false,
    disabled:   false,
    loading:    false
  };

  getHeader() {
    if (this.props.header.content) {
      return <Header {...this.props.header} />;
    }

    return null;
  }

  render() {
    const { disabled, loading, stacked, raised, horizontal, vertical, piled, className, children } = this.props;
    const additionalClasses = {
      disabled,
      loading,
      raised,
      horizontal,
      vertical,
      piled,
      stacked
    };
    return (
      <div className={classNames('ui', 'segment', className, additionalClasses)}>
        {this.getHeader()}
        {children}
      </div>
    );
  }
}

export default Segment;
