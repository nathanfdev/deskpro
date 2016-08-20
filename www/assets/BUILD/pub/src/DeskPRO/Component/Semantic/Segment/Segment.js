import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Header } from 'DeskPRO/Component/Semantic/Common';

class Segment extends React.Component {

  static propTypes = {
    children:  PropTypes.object.isRequired,
    className: PropTypes.arrayOf(PropTypes.string),
    header:    PropTypes.shape({
      size:    PropTypes.integer,
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
    classes: [],
    header:  {
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
    const additionaClasses = {
      disabled,
      loading,
      raised,
      horizontal,
      vertical,
      piled,
      stacked
    };
    return (
      <div className={classNames('ui', 'segment', className, additionaClasses)}>
        {this.getHeader()}
        {children}
      </div>
    );
  }
}

export default Segment;
