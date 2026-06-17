import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M2.8 7.2C9 3.4 15 3.4 21.2 7.2L12.7 21.4C12.3 22.1 11.7 22.1 11.3 21.4ZM7.95 9.2a1.05 1.05 0 1 0 2.1 0 1.05 1.05 0 1 0 -2.1 0ZM13.35 9.4a1.05 1.05 0 1 0 2.1 0 1.05 1.05 0 1 0 -2.1 0ZM10.55 13.2a1.15 1.15 0 1 0 2.3 0 1.15 1.15 0 1 0 -2.3 0Z"
            />
        </svg>
    );
}
