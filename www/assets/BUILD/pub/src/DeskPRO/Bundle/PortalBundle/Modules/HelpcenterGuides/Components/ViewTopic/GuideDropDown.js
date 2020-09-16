import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';

class GuideDropDown extends React.PureComponent {
  static propTypes = {
    activeGuide: PropTypes.object,
    style:       PropTypes.object,
    guides:      PropTypes.array,
    selectGuide: PropTypes.func,
  }

  constructor(props) {
    super(props);
    this.state = {
      opened: false
    };
  }


  onClickGuide = (e, guide) => {
    e.preventDefault();
    this.setState({
      opened: false
    });
    this.props.selectGuide(guide);
  };

  toggleMenu = () => {
    this.setState({
      opened: !this.state.opened
    });
  }

  renderDropDownGuide = (guide, baseUrl) => (
    <li key={guide.id}>
      <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-dropdown-link" onClick={e => this.onClickGuide(e, guide)}>
        <figure className="dp-po-icon" style={{ backgroundColor: guide.color ? `#${guide.color}` : 'var(--warning)' }}><IconRenderer object={guide} className="" default={<i className="fal fa-user-headset" />} /></figure> {guide.title}
      </a>
    </li>
  )

  render() {
    const { activeGuide, guides, style } = this.props;
    const { opened } = this.state;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    return (
      <div className={classNames('dp-po-guides-dropdown', { opened })} style={style}>
        <button className="dp-po-guides-dropdown-button" onClick={this.toggleMenu}>
          <figure className="dp-po-icon" style={{ backgroundColor: activeGuide.color ? `#${activeGuide.color}` : 'var(--warning)' }}><IconRenderer object={activeGuide} className="" default={<i className="fal fa-user-headset" />} /></figure>
          {activeGuide.title}
          <i className="fal fa-angle-down" />
        </button>
        <div className="dp-po-guides-dropdown-menu" style={{ display: opened ? 'block' : 'none' }}>
          <ul>
            {guides.map(guide => this.renderDropDownGuide(guide, baseUrl))}
          </ul>
        </div>
      </div>
    );
  }
}

export default GuideDropDown;
