import React from 'react';
import PropTypes from 'prop-types';
import $ from 'jquery';
import classNames from 'classnames';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import Isvg from 'react-inlinesvg';
import guideDefault from '@deskpro/portal-style/dist/img/page-icons/guide-default.svg';
import allGuides from '@deskpro/portal-style/dist/img/page-icons/all-guides.svg';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { FormattedMessage } from 'react-intl';

class GuideDropDown extends React.PureComponent {
  static propTypes = {
    activeGuide: PropTypes.object,
    style:       PropTypes.object,
    sizes:       PropTypes.object,
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

  onClickOut = (e) => {
    // We only close the menu if we are not clicking on it, the next function will do it otherwise
    if ($(e.target).parents('.dp-po-guides-dropdown-button').length === 0) {
      this.setState({ opened: false });
    }
  }

  toggleMenu = () => {
    this.setState({
      opened: !this.state.opened
    });
  }

  renderDropDownGuide = (guide, baseUrl) => (
    <li key={guide.id} title={guide.title}>
      <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-dropdown-link" onClick={e => this.onClickGuide(e, guide)}>
        <IconRenderer object={guide} className="" default={<Isvg src={guideDefault} />} figureStyle={{ backgroundColor: guide.color ? `#${guide.color}` : 'var(--warning)' }} /> {guide.title}
      </a>
    </li>
  )

  render() {
    const { activeGuide, guides, style, sizes } = this.props;
    const { opened } = this.state;

    const buttonStyle = {};
    if (sizes) {
      buttonStyle.width = sizes.searchWidth;
    }

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    return (
      <div className={classNames('dp-po-guides-dropdown', { opened })} style={style}>
        <button className="dp-po-guides-dropdown-button" onClick={this.toggleMenu} style={buttonStyle}>
          <IconRenderer object={activeGuide} className="" default={<Isvg src={guideDefault} />} figureStyle={{ backgroundColor: activeGuide.color ? `#${activeGuide.color}` : 'var(--warning)' }} />
          <span>{activeGuide.title}</span>
          <FontAwesomeIcon icon={['fal', 'angle-down']} />
        </button>
        <ClickOut onClickOut={this.onClickOut}>
          <div className="dp-po-guides-dropdown-menu" style={{ display: opened ? 'block' : 'none' }}>
            <ul>
              {guides.map(guide => this.renderDropDownGuide(guide, baseUrl))}
            </ul>
            <div className="all-guides">
              <a href={`${baseUrl}/guides`} className="dp-po-guides-dropdown-link">
                <figure className="dp-po-icon" style={{ background: 'none' }}>
                  <Isvg src={allGuides} />
                </figure>
                <FormattedMessage id="helpcenter.guides.view_all_guides" />
              </a>
            </div>
          </div>
        </ClickOut>
      </div>
    );
  }
}

export default GuideDropDown;
