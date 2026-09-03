export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js}',
    ],
    theme: {
        extend: {
            colors: {
                canvas: '#F5F6F8',
                surface: '#FFFFFF',
                ink: {
                    DEFAULT: '#14171F',
                    soft: '#4B5163',
                    faint: '#8A90A2',
                },
                border: {
                    DEFAULT: '#DEE1E7',
                    strong: '#C3C8D2',
                },
                brand: {
    50:  '#f0f9f6',
    100: '#dff1ea',
    200: '#b9dfcf',
    300: '#87c5ab',
    400: '#57a886',
    500: '#3c8667',
    600: '#2c6d52',
    700: '#1f513c',
    800: '#143929',
    900: '#0c2219',
},
                amber: {
                    50: '#FBF2E3',
                    300: '#DDA958',
                    500: '#B4790A',
                    700: '#8A5C08',
                },
                moss: {
                    50: '#E9F3EC',
                    300: '#7FB496',
                    500: '#2F7A52',
                    700: '#215C3D',
                },
                brick: {
                    50: '#F8E9E9',
                    300: '#D18C8C',
                    500: '#B23B3B',
                    700: '#8A2C2C',
                },
                violet: {
                    50: '#EFEAF7',
                    300: '#AB98D8',
                    500: '#6A4FB3',
                    700: '#4F3985',
                },
            },
            fontFamily: {
    sans: ['Inter', 'sans-serif'],
    display: ['Merriweather', 'serif'],
    mono: ['JetBrains Mono', 'monospace'],
},
            boxShadow: {
                panel: '0 1px 2px rgba(20, 23, 31, 0.06)',
                popover: '0 8px 24px rgba(13, 27, 44, 0.16)',
            },
            borderRadius: {
                sm: '4px',
                DEFAULT: '6px',
                md: '8px',
            },
        },
    },
    plugins: [],
};
